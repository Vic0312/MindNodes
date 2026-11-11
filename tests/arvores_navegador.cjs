// Node 22+, PHP CLI e Chrome locais; sem dependências npm ou alterações no banco.
'use strict';
const { spawn } = require('node:child_process');
const { mkdtemp, writeFile, readFile } = require('node:fs/promises');
const { tmpdir } = require('node:os');
const path = require('node:path');
const net = require('node:net');
const assert = require('node:assert/strict');
(async () => {
    const dir = await mkdtemp(path.join(tmpdir(), 'mindnodes_arvores_'));
    await writeFile(path.join(dir, 'sess_testearvores'), 'usuario_id|i:1;usuario_nome|s:5:"Teste";estaLogado|b:1;');
    const socket = net.createServer();
    await new Promise(resolve => socket.listen(0, '127.0.0.1', resolve));
    const port = socket.address().port;
    await new Promise(resolve => socket.close(resolve));
    const base = `http://127.0.0.1:${port}`;
    const php = spawn(process.env.MINDNODES_PHP || 'php', ['-d', `session.save_path=${dir}`, '-S', `127.0.0.1:${port}`, '-t', path.resolve(__dirname, '..')], { windowsHide: true, stdio: 'ignore' });
    const chrome = spawn(process.env.MINDNODES_CHROME || 'C:/Program Files/Google/Chrome/Application/chrome.exe', ['--headless=new', '--disable-gpu', '--no-first-run', '--no-default-browser-check', '--remote-debugging-port=0', `--user-data-dir=${path.join(dir, 'chrome')}`, 'about:blank'], { windowsHide: true, stdio: 'ignore' });
    let ws;
    try {
        let debugPort;
        for (let i = 0; i < 100; i++) {
            try { debugPort = (await readFile(path.join(dir, 'chrome', 'DevToolsActivePort'), 'utf8')).split('\n')[0]; await fetch(base); break; }
            catch { await new Promise(resolve => setTimeout(resolve, 100)); }
        }
        assert(debugPort, 'Chrome indisponível');
        for (const page of ['arvores.php', 'simulador_arvores.php']) {
            assert.equal((await fetch(`${base}/view/${page}`, { redirect: 'manual' })).status, 302);
            for (const type of ['binaria', 'trie', 'patricia', 'avl', 'invalido', 'binaria&tipo[]=x']) {
                const response = await fetch(`${base}/view/${page}?tipo=${type}`, { headers: { Cookie: 'PHPSESSID=testearvores' } });
                assert.equal(response.status, 200);
                assert(!/Fatal error|Warning:|Parse error/.test(await response.text()));
            }
        }
        const pages = await (await fetch(`http://127.0.0.1:${debugPort}/json`)).json();
        ws = new WebSocket(pages.find(p => p.type === 'page').webSocketDebuggerUrl);
        await new Promise((resolve, reject) => { ws.onopen = resolve; ws.onerror = reject; });
        let id = 0;
        const pending = new Map(), errors = [];
        ws.onmessage = event => {
            const result = JSON.parse(event.data);
            if (result.method === 'Runtime.exceptionThrown') errors.push(result.params);
            if (pending.has(result.id)) {
                const item = pending.get(result.id); clearTimeout(item.timer); pending.delete(result.id);
                result.error ? item.reject(new Error(JSON.stringify(result.error))) : item.resolve(result.result);
            }
        };
        const send = (method, params = {}) => new Promise((resolve, reject) => {
            const key = ++id;
            const timer = setTimeout(() => { pending.delete(key); reject(new Error(`Timeout: ${method}`)); }, 15000);
            pending.set(key, { resolve, reject, timer }); ws.send(JSON.stringify({ id: key, method, params }));
        });
        const evaluate = async expression => {
            const result = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
            assert(!result.exceptionDetails, JSON.stringify(result.exceptionDetails)); return result.result.value;
        };
        const wait = async expression => {
            for (let i = 0; i < 100; i++) { if (await evaluate(expression)) return; await new Promise(resolve => setTimeout(resolve, 100)); }
            throw new Error('Condição não alcançada: ' + expression);
        };
        await send('Runtime.enable'); await send('Network.enable');
        await send('Network.setBlockedURLs', { urls: ['*fonts.googleapis.com*', '*fonts.gstatic.com*'] });
        await send('Network.setCookie', { name: 'PHPSESSID', value: 'testearvores', url: base });
        const click = id => evaluate(`document.getElementById('arvore-${id}').click()`);
        const value = v => evaluate(`document.getElementById('arvore-valor').value = ${JSON.stringify(String(v))}`);
        const operation = name => evaluate(`document.querySelector('[data-operacao="${name}"]').click()`);
        const finish = () => click('concluir');
        for (const width of [320, 375, 768, 1280]) {
            await send('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: width < 768 });
            await send('Page.navigate', { url: base + '/view/simulador_arvores.php' });
            await wait('document.readyState === "complete" && !!document.getElementById("arvore-limpar") && !document.getElementById("arvore-limpar").disabled');
            await value(7); await operation('buscar');
            assert.match(await evaluate('document.getElementById("arvore-mensagem").textContent'), /vazia/);
            await click('exemplo');
            assert.equal(await evaluate('document.querySelectorAll(".arvore-no").length'), 3);
            await value(5); await operation('buscar');
            assert(await evaluate('document.querySelector("[data-operacao=inserir]").disabled'));
            await click('proximo'); await click('anterior'); await finish();
            assert.equal(await evaluate('document.querySelectorAll(".arvore-visitado").length'), 3);
            assert.equal(await evaluate('document.querySelector(".arvore-resultado .arvore-valor").textContent'), '5');
            await value(10); await operation('remover'); await finish();
            assert.equal(await evaluate('document.querySelectorAll(".arvore-no").length'), 2);
            assert.equal(await evaluate('document.querySelector(".arvore-no .arvore-valor").textContent'), '5');
            await value(30); await operation('inserir');
            assert.match(await evaluate('document.getElementById("arvore-mensagem").textContent'), /ocupado/);
            await evaluate('document.getElementById("arvore-lado").value="right"');
            await operation('inserir'); await finish();
            assert.equal(await evaluate('document.querySelectorAll(".arvore-no").length'), 3);
            await value(1.5); await operation('buscar');
            assert.match(await evaluate('document.getElementById("arvore-mensagem").textContent'), /inteiro/);
            await value(999); await operation('buscar'); await click('limpar');
            assert.equal(await evaluate('document.querySelectorAll(".arvore-no").length'), 0);
            // A wide, skewed tree must scroll inside its own region, not widen the page.
            for (let n = 0; n < 12; n++) {
                await value(n);
                if (n) await evaluate('(() => { const p=document.getElementById("arvore-pai"); p.selectedIndex=p.options.length-1; document.getElementById("arvore-lado").value="right"; })()');
                await operation('inserir'); await finish();
            }
            const layout = await evaluate(`(() => {
                const svg=document.getElementById('arvore-svg'), box=svg.viewBox.baseVal;
                const rects=[...svg.querySelectorAll('.arvore-no rect')].map(e=>({x:+e.getAttribute('x'),y:+e.getAttribute('y'),w:+e.getAttribute('width'),h:+e.getAttribute('height')}));
                return {fits:document.documentElement.scrollWidth<=innerWidth, inside:rects.every(r=>r.x>=0&&r.y>=0&&r.x+r.w<=box.width&&r.y+r.h<=box.height), separate:rects.every((r,i)=>rects.every((s,j)=>i===j||r.x+r.w<=s.x||s.x+s.w<=r.x||r.y+r.h<=s.y||s.y+s.h<=r.y))};
            })()`);
            assert(layout.fits && layout.inside && layout.separate, JSON.stringify({ width, layout }));
            console.log(`PASSOU: botões, passos, entradas, cancelamento, desenho e responsividade ${width}px.`);
            for (const type of ['binaria', 'trie', 'patricia', 'avl']) {
                await send('Page.navigate', { url: `${base}/view/arvores.php?tipo=${type}` });
                await wait('document.readyState === "complete" && !!document.querySelector(".arvore-esquema")');
                assert(await evaluate('document.documentElement.scrollWidth <= innerWidth'), `Aula ${type}, ${width}px`);
            }
        }
        for (const type of ['trie', 'patricia', 'avl']) {
            await send('Page.navigate', { url: `${base}/view/simulador_arvores.php?tipo=${type}` });
            await wait('document.readyState === "complete" && !!document.querySelector("fieldset")');
            assert(await evaluate('document.querySelector("fieldset").disabled && !document.getElementById("arvore-form")'));
        }
        await send('Page.navigate', { url: base + '/view/simulador.php' });
        await wait('document.readyState === "complete" && typeof adicionarFim === "function"');
        await evaluate('document.getElementById("valorNo").value=77; adicionarFim();');
        assert(await evaluate('document.getElementById("lista").textContent.includes("77")'));
        assert.equal(errors.length, 0, JSON.stringify(errors));
        console.log('PASSOU: autenticação, quatro aulas, telas futuras desabilitadas, simulador anterior e ausência de exceções JavaScript.');
        await send('Browser.close');
    } finally {
        if (ws) ws.close(); chrome.kill(); php.kill();
        console.log(`Artefatos temporários de diagnóstico: ${dir}`);
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
