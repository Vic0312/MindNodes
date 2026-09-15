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
        await enviar('Network.enable');
        // Testa o componente local; não depende da reprodução externa.
        await enviar('Network.setBlockedURLs', { urls: ['*youtube*', '*googlevideo*'] });
        await enviar('Network.setCookie', { name: 'PHPSESSID', value: 'testemidia', url: base });
        const paginasAula = ['estruturas.php', 'fila_fifo.php', 'fila_prioridade.php', 'pilha_encadeada.php'];
        const ultimos = { tad: [20, 10], simples: [5, 10, 30], dupla: [10, 30, 40], 'fila-fifo': [], 'fila-prioridade': [10, 30, 40], 'pilha-encadeada': [] };
        for (const largura of [320, 375, 768, 1280]) {
            await enviar('Emulation.setDeviceMetricsOverride', { width: largura, height: 900, deviceScaleFactor: 1, mobile: largura < 768 });
            for (const pagina of paginasAula) {
                await enviar('Page.navigate', { url: base + '/view/' + pagina });
                await esperar('document.readyState === "complete" && !!document.querySelector("[data-iniciada]")');
                const ids = await avaliar('[...document.querySelectorAll("[data-midia]")].map(e => e.id)');
                for (const id of ids) {
                    const seletor = `document.getElementById('${id}')`;
                    const quantidade = await avaliar(`${seletor}.querySelectorAll('.midia-passo').length`);
                    assert(await avaliar(`${seletor}.querySelector('[data-midia-anterior]').disabled`));
                    for (let passo = 0; passo < quantidade; passo++) {
                        const estado = await avaliar(`(() => {
                            const aula = ${seletor};
                            const visiveis = [...aula.querySelectorAll('.midia-passo')].filter(e => !e.hidden);
                            return { quantidade: visiveis.length,
                                indice: [...aula.querySelectorAll('.midia-passo')].indexOf(visiveis[0]),
                                cabe: document.documentElement.scrollWidth <= innerWidth,
                                rolagem: [...visiveis[0].querySelectorAll('.midia-cadeia')].every(e => getComputedStyle(e).overflowX === 'auto'),
                                valores: [...visiveis[0].querySelectorAll('.midia-no > strong')].map(e => Number(e.textContent)) };
                        })()`);
                        assert(estado.cabe && estado.rolagem && estado.quantidade === 1 && estado.indice === passo, `${id} ${largura}: ${JSON.stringify(estado)}`);
                        if (passo === quantidade - 1) assert.deepEqual(estado.valores, ultimos[id.replace('midia-', '')]);
                        else await avaliar(`${seletor}.querySelector('[data-midia-proximo]').click()`);
                    }
                    assert(await avaliar(`${seletor}.querySelector('[data-midia-proximo]').disabled`));
                    await avaliar(`${seletor}.querySelector('[data-midia-anterior]').click()`);
                    assert(await avaliar(`${seletor}.querySelector('.midia-status').textContent.includes('Passo ${quantidade - 1} de')`));
                    await avaliar(`${seletor}.querySelector('[data-midia-todos]').click()`);
                    assert.equal(await avaliar(`${seletor}.querySelectorAll('.midia-passo:not([hidden])').length`), quantidade);
                    await avaliar(`${seletor}.querySelector('[data-midia-reiniciar]').click()`);
                    assert.equal(await avaliar(`${seletor}.querySelectorAll('.midia-passo:not([hidden])').length`), 1);
                    if (await avaliar(`!!${seletor}.querySelector('[data-midia-video]')`)) {
                        assert.equal(await avaliar(`${seletor}.querySelectorAll('iframe').length`), 0);
                        await avaliar(`${seletor}.querySelector('[data-midia-video]').click()`);
                        const iframe = await avaliar(`(() => { const e = ${seletor}.querySelector('iframe'); return { src:e.src, title:e.title }; })()`);
                        assert(iframe.src.startsWith('https://www.youtube-nocookie.com/embed/') && iframe.src.endsWith('?autoplay=0') && iframe.title);
                        await avaliar(`${seletor}.querySelector('[data-midia-video]').click()`);
                        assert.equal(await avaliar(`${seletor}.querySelectorAll('iframe').length`), 0);
                    }
                }
            }
            console.log(`PASSOU: seis estruturas a ${largura}px, ordem dos nós, controles, rolagem e vídeo sob demanda.`);
        }
        // Volta ao documento principal após abrir/fechar os frames de vídeo.
        await enviar('Page.navigate', { url: base + '/view/pilha_encadeada.php' });
        await esperar('document.readyState === "complete" && !!document.querySelector("[data-iniciada]")');
        await enviar('Page.bringToFront');
        await enviar('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'reduce' }] });
        assert.equal(await avaliar('getComputedStyle(document.querySelector(".midia-passo:not([hidden])")).animationName'), 'none');
        await avaliar('document.querySelector("[data-midia-proximo]").focus()');
        await enviar('Input.dispatchKeyEvent', { type: 'keyDown', key: 'Enter', code: 'Enter', text: '\r', windowsVirtualKeyCode: 13 });
        await enviar('Input.dispatchKeyEvent', { type: 'keyUp', key: 'Enter', code: 'Enter', windowsVirtualKeyCode: 13 });
        const teclado = await avaliar('({status: document.querySelector(".midia-status").textContent, foco: document.activeElement.outerHTML, url: location.href})');
        assert(teclado.status.includes('Passo 2 de'), JSON.stringify(teclado));
        await enviar('Emulation.setScriptExecutionDisabled', { value: true });
        await enviar('Page.navigate', { url: base + '/view/estruturas.php' });
        await esperar('document.readyState === "complete" && !!document.querySelector("#midia-tad")');
        assert(await avaliar('[...document.querySelectorAll(".midia-passo")].every(e => !e.hidden)'));
        assert(await avaliar('[...document.querySelectorAll(".midia-controles")].every(e => e.hidden)'));
        assert.equal(erros.length, 0, JSON.stringify(erros));
        console.log('PASSOU: teclado, movimento reduzido, fallback sem JavaScript e ausência de erros JS.');
        await enviar('Browser.close');
    } finally {
        if (ws) ws.close();
        navegador.kill();
        if (navegador.exitCode === null) await new Promise(resolve => navegador.once('exit', resolve));
    }
})().catch(erro => { console.error(erro); process.exitCode = 1; });
