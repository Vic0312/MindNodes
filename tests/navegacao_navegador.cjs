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
        const cookie = process.argv[4];
        const saldo = process.argv[5];
        for (const autenticado of [false, true]) {
            if (autenticado) await enviar('Network.setCookie', { name: 'PHPSESSID', value: cookie, url: base });
            else await enviar('Network.clearBrowserCookies');
            for (const largura of [320, 375, 768, 1280]) {
                await enviar('Emulation.setDeviceMetricsOverride', { width: largura, height: 900, deviceScaleFactor: 1, mobile: largura < 768 });
                for (const pagina of (autenticado ? ['home', 'perfil'] : ['home'])) {
                    await enviar('Page.navigate', { url: base + '/view/' + pagina + '.php' });
                    await esperar(`document.readyState === 'complete' && location.pathname.endsWith('/${pagina}.php') && !!document.querySelector('.mn-header')`);
                    if (largura <= 650) {
                        assert(await avaliar('document.getElementById("mn-menu").hidden'));
                        await avaliar('document.querySelector(".mn-toggle").click()');
                        assert(await avaliar('!document.getElementById("mn-menu").hidden && document.querySelector(".mn-toggle").getAttribute("aria-expanded") === "true"'));
                    }
                    await avaliar('document.querySelector(".mn-estruturas").open = true');
                    const estado = await avaliar(`(() => ({
                        largura: innerWidth, documento: document.documentElement.scrollWidth,
                        links: [...document.querySelectorAll('.mn-links a')].map(e => e.textContent),
                        estruturas: document.querySelectorAll('.mn-estruturas a').length,
                        ativo: document.querySelector('.mn-ativo')?.getAttribute('href'),
                        saldo: document.querySelector('[data-saldo-navegacao]')?.textContent,
                        senha: document.querySelector('input[type=password]')?.value || '',
                        foco: getComputedStyle(document.querySelector('.mn-ativo')).textDecorationLine
                    }))()`);
                    assert(estado.documento <= estado.largura, `${pagina} ${largura}: ${JSON.stringify(estado)}`);
                    assert.equal(estado.estruturas, 6);
                    assert.equal(estado.ativo, pagina + '.php');
                    assert(estado.foco.includes('underline'));
                    assert.equal(estado.senha, '');
                    if (autenticado) {
                        assert.equal(estado.saldo, saldo + ' moedas');
                        assert(estado.links.includes('Sair') && estado.links.includes('Avatar') && !estado.links.includes('Login'));
                    } else assert(estado.links.includes('Login') && estado.links.includes('Cadastro') && !estado.saldo);
                    if (largura <= 650) {
                        await enviar('Page.bringToFront');
                        await avaliar('document.querySelector(".mn-links a").focus()');
                        await enviar('Input.dispatchKeyEvent', { type: 'keyDown', key: 'Escape', code: 'Escape', windowsVirtualKeyCode: 27 });
                        await enviar('Input.dispatchKeyEvent', { type: 'keyUp', key: 'Escape', code: 'Escape', windowsVirtualKeyCode: 27 });
                        assert(await avaliar('document.getElementById("mn-menu").hidden && document.activeElement.classList.contains("mn-toggle")'));
                    }
                }
                console.log(`OK: ${autenticado ? 'logado Home/Perfil' : 'visitante Home'} ${largura}px, menu, saldo, estruturas e teclado.`);
            }
        }
        await enviar('Emulation.setScriptExecutionDisabled', { value: true });
        await enviar('Page.navigate', { url: base + '/view/home.php' });
        await esperar('document.readyState === "complete" && !!document.querySelector(".mn-header")');
        assert(await avaliar('!document.getElementById("mn-menu").hidden'));
        assert.equal(erros.length, 0, JSON.stringify(erros));
        console.log('OK: menu acessível sem JavaScript e nenhuma exceção JS.');
        await enviar('Browser.close');
    } finally {
        if (ws) ws.close();
        navegador.kill();
        if (navegador.exitCode === null) await new Promise(resolve => navegador.once('exit', resolve));
    }
})().catch(erro => { console.error(erro); process.exitCode = 1; });
