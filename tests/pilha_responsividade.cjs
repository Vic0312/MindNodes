// Invocado pelo teste PHP com a página renderizada; não produz imagens.
const { spawn } = require('node:child_process');
const { pathToFileURL } = require('node:url');
const { readFile } = require('node:fs/promises');
const path = require('node:path');
const assert = require('node:assert/strict');

(async () => {
    const pasta = process.argv[2];
    const navegador = spawn(process.env.MINDNODES_CHROME || 'C:/Program Files/Google/Chrome/Application/chrome.exe', [
        '--headless=new', '--disable-gpu', '--no-first-run', '--no-default-browser-check',
        '--remote-debugging-port=0', '--user-data-dir=' + path.join(pasta, 'chrome'), 'about:blank'
    ], { windowsHide: true, stdio: 'ignore' });
    let ws;
    try {
        let porta;
        for (let tentativa = 0; tentativa < 100; tentativa++) {
            try { porta = (await readFile(path.join(pasta, 'chrome', 'DevToolsActivePort'), 'utf8')).split('\n')[0]; break; }
            catch { await new Promise(resolve => setTimeout(resolve, 100)); }
        }
        assert(porta, 'Chrome não iniciou');
        const paginas = await (await fetch(`http://127.0.0.1:${porta}/json`)).json();
        ws = new WebSocket(paginas.find(p => p.type === 'page').webSocketDebuggerUrl);
        await new Promise((resolve, reject) => { ws.onopen = resolve; ws.onerror = reject; });
        let id = 0;
        const pendentes = new Map();
        ws.onmessage = e => {
            const resposta = JSON.parse(e.data);
            if (pendentes.has(resposta.id)) {
                const { resolve, reject } = pendentes.get(resposta.id);
                pendentes.delete(resposta.id);
                resposta.error ? reject(new Error(JSON.stringify(resposta.error))) : resolve(resposta.result);
            }
        };
        const enviar = (method, params = {}) => new Promise((resolve, reject) => {
            pendentes.set(++id, { resolve, reject });
            ws.send(JSON.stringify({ id, method, params }));
        });
        await enviar('Page.navigate', { url: pathToFileURL(path.join(pasta, 'aula.html')).href });
        for (let tentativa = 0; tentativa < 50; tentativa++) {
            const pronto = await enviar('Runtime.evaluate', { expression: 'document.readyState === "complete" && !!document.querySelector(".aula-pilha")', returnByValue: true });
            if (pronto.result.value) break;
            await new Promise(resolve => setTimeout(resolve, 100));
        }
        for (const largura of [320, 375, 768, 1280]) {
            await enviar('Emulation.setDeviceMetricsOverride', { width: largura, height: 900, deviceScaleFactor: 1, mobile: largura < 768 });
            const resultado = await enviar('Runtime.evaluate', { returnByValue: true, expression: `JSON.stringify({
                largura: innerWidth, documento: document.documentElement.scrollWidth,
                diagramas: [...document.querySelectorAll('.pilha-diagrama')].every(e => getComputedStyle(e).flexDirection === 'column' && e.getBoundingClientRect().right <= innerWidth),
                codigo: [...document.querySelectorAll('pre')].every(e => getComputedStyle(e).overflowX === 'auto' && getComputedStyle(e.querySelector('code')).whiteSpace === 'pre'),
                cards: getComputedStyle(document.querySelector('.fila-grade')).gridTemplateColumns,
                tabela: getComputedStyle(document.querySelector('.fila-tabela')).overflowX
            })` });
            const dados = JSON.parse(resultado.result.value);
            assert(dados.documento <= dados.largura, JSON.stringify(dados));
            assert(dados.diagramas && dados.codigo && dados.tabela === 'auto', JSON.stringify(dados));
            if (largura < 768) assert(!dados.cards.includes(' '), JSON.stringify(dados));
            console.log(`PASSOU: layout ${largura}px; sem overflow da página, diagramas verticais, código e tabela com rolagem.`);
        }
        await enviar('Browser.close');
    } finally {
        if (ws) ws.close();
        navegador.kill();
        if (navegador.exitCode === null) await new Promise(resolve => navegador.once('exit', resolve));
    }
})().catch(erro => { console.error(erro); process.exitCode = 1; });
