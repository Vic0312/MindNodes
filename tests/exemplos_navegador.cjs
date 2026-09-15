// Chrome headless via protocolo nativo: sem bibliotecas ou imagens geradas.
const { spawn } = require('node:child_process');
const { readFile } = require('node:fs/promises');
const path = require('node:path');
const assert = require('node:assert/strict');

(async () => {
    const [pasta, base] = process.argv.slice(2);
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
        const erros = [];
        ws.onmessage = e => {
            const resposta = JSON.parse(e.data);
            if (resposta.method === 'Runtime.exceptionThrown') erros.push(resposta.params);
            if (pendentes.has(resposta.id)) {
                const { resolve, reject, timer } = pendentes.get(resposta.id);
                clearTimeout(timer);
                pendentes.delete(resposta.id);
                resposta.error ? reject(new Error(JSON.stringify(resposta.error))) : resolve(resposta.result);
            }
        };
        const enviar = (method, params = {}) => new Promise((resolve, reject) => {
            const atual = ++id;
            const timer = setTimeout(() => { pendentes.delete(atual); reject(new Error('Timeout ' + method)); }, 10000);
            pendentes.set(atual, { resolve, reject, timer });
            ws.send(JSON.stringify({ id: atual, method, params }));
        });
        const avaliar = async expression => {
            const retorno = await enviar('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
            assert(!retorno.exceptionDetails, JSON.stringify(retorno.exceptionDetails));
            return retorno.result.value;
        };
        const esperar = async expression => {
            for (let i = 0; i < 60; i++) {
                if (await avaliar(expression)) return;
                await new Promise(resolve => setTimeout(resolve, 100));
            }
            throw new Error('Condição não alcançada: ' + expression);
        };
        await enviar('Runtime.enable');
        await enviar('Network.setCookie', { name: 'PHPSESSID', value: 'testeexemplos', url: base });
        await enviar('Page.navigate', { url: base + '/view/exemplos.php' });
        await esperar('document.readyState === "complete" && !!document.querySelector("#dados-exemplos")');
        const slugs = ['tad', 'simples', 'dupla', 'fila-fifo', 'fila-prioridade', 'pilha-encadeada'];
        for (const largura of [320, 375, 768, 1280]) {
            await enviar('Emulation.setDeviceMetricsOverride', { width: largura, height: 900, deviceScaleFactor: 1, mobile: largura < 768 });
            for (const slug of slugs) {
                await avaliar(`document.querySelector('[data-exemplo="${slug}"]').click()`);
                const dados = await avaliar(`(() => {
                    const exemplo = JSON.parse(document.getElementById('dados-exemplos').textContent)['${slug}'];
                    return {
                        largura: innerWidth, documento: document.documentElement.scrollWidth,
                        titulo: document.getElementById('titulo-codigo').textContent === exemplo.titulo,
                        texto: document.getElementById('codigo-exemplo').textContent === exemplo.codigo && document.getElementById('uso-exemplo').textContent === exemplo.uso && document.getElementById('saida-exemplo').textContent === exemplo.saida,
                        pre: [...document.querySelectorAll('pre')].every(e => getComputedStyle(e).overflowX === 'auto' && getComputedStyle(e.querySelector('code')).whiteSpace === 'pre' && e.tabIndex === 0),
                        ativo: document.querySelectorAll('[aria-current="page"]').length === 1 && document.querySelector('[aria-current="page"]').dataset.exemplo === '${slug}',
                        sublinhado: getComputedStyle(document.querySelector('[aria-current="page"]')).textDecorationLine.includes('underline'),
                        links: document.getElementById('teoria-exemplo').getAttribute('href') === exemplo.teoria && document.getElementById('quiz-exemplo').getAttribute('href') === 'quiz.php?assunto=' + exemplo.quiz,
                        url: new URLSearchParams(location.search).get('estrutura') === '${slug}'
                    };
                })()`);
                assert(dados.documento <= dados.largura, `${slug} ${largura}: ${JSON.stringify(dados)}`);
                assert(dados.titulo && dados.texto && dados.pre && dados.ativo && dados.sublinhado && dados.links && dados.url, JSON.stringify(dados));
            }
            console.log(`PASSOU: seis seleções a ${largura}px; conteúdo, links, estado ativo e rolagem do código.`);
        }
        await avaliar('history.back()');
        await esperar('document.querySelector("[aria-current=page]").dataset.exemplo === "fila-prioridade"');
        await avaliar('history.forward()');
        await esperar('document.querySelector("[aria-current=page]").dataset.exemplo === "pilha-encadeada"');
        await avaliar('document.querySelector("[data-exemplo=simples]").focus()');
        await enviar('Input.dispatchKeyEvent', { type: 'keyDown', key: 'Enter', code: 'Enter', windowsVirtualKeyCode: 13 });
        await enviar('Input.dispatchKeyEvent', { type: 'keyUp', key: 'Enter', code: 'Enter', windowsVirtualKeyCode: 13 });
        await esperar('document.querySelector("[aria-current=page]").dataset.exemplo === "simples"');
        await enviar('Page.reload');
        await esperar('document.readyState === "complete" && document.querySelector("[aria-current=page]")?.dataset.exemplo === "simples"');
        assert.equal(erros.length, 0, JSON.stringify(erros));
        console.log('PASSOU: teclado, voltar/avançar, recarregar seleção e ausência de erros JavaScript.');
        await enviar('Browser.close');
    } finally {
        if (ws) ws.close();
        navegador.kill();
        if (navegador.exitCode === null) await new Promise(resolve => navegador.once('exit', resolve));
    }
})().catch(erro => { console.error(erro); process.exitCode = 1; });
