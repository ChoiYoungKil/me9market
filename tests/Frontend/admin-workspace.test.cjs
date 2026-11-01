const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname, '../../public/common/admin-workspace.js'), 'utf8');

function runtime() {
    const handlers = {};
    const message = {textContent: '', setAttribute() {}};
    const busy = {hidden: true, setAttribute() {}, querySelector() { return message; }};
    const body = {append(node) { node.parentElement = body; }};
    let timer;
    const context = {
        document: {addEventListener(name, callback) { (handlers[name] ??= []).push(callback); },
            createElement() { return busy; }, body, querySelector() { return null; }},
        location: {origin: 'http://127.0.0.1:8000', reload() {}},
        URL, Headers, AbortController, Set, Symbol,
        setTimeout(fn) { timer = fn; return 1; }, clearTimeout() { timer = null; }, addEventListener() {}
    };
    context.window = context;
    vm.createContext(context);
    vm.runInContext(source, context);
    return {context, busy, handlers, expire() { timer(); }};
}

for (const [label, response] of [
    ['success', () => new Response(JSON.stringify({status: true}), {headers: {'Content-Type': 'application/json'}})],
    ['419', () => new Response(JSON.stringify({message: 'Expired'}), {status: 419, headers: {'Content-Type': 'application/json'}})],
    ['500', () => new Response(JSON.stringify({message: 'Failed'}), {status: 500, headers: {'Content-Type': 'application/json'}})],
    ['invalid JSON', () => new Response('{invalid', {headers: {'Content-Type': 'application/json'}})],
    ['business rejection', () => new Response(JSON.stringify({status: false, message: 'Rejected'}), {headers: {'Content-Type': 'application/json'}})]
]) {
    test(`loading is released after ${label}`, async () => {
        const {context, busy} = runtime(); context.fetch = async () => response();
        if (label === 'success') await context.adminRequest('/admin/workspace/summary');
        else await assert.rejects(context.adminRequest('/admin/workspace/summary'));
        assert.equal(busy.hidden, true);
    });
}

test('timeout aborts the request and releases the overlay', async () => {
    const {context, busy, expire} = runtime();
    context.fetch = (_url, options) => new Promise((_resolve, reject) => options.signal.addEventListener('abort', () => {
        const error = new Error('aborted'); error.name = 'AbortError'; reject(error);
    }));
    const pending = context.adminRequest('/admin/workspace/summary');
    assert.equal(busy.hidden, false); expire();
    await assert.rejects(pending, error => error.message.includes('응답 시간이 초과'));
    assert.equal(busy.hidden, true);
});

test('cross-origin requests are blocked and concurrent requests keep their own loading token', async () => {
    const {context, busy} = runtime();
    await assert.rejects(context.adminRequest('https://example.test'), error => error.message.includes('같은 사이트'));
    const one = context.AdminBusy.show(); const two = context.AdminBusy.show();
    context.AdminBusy.hide(one); assert.equal(busy.hidden, false);
    context.AdminBusy.hide(two); assert.equal(busy.hidden, true);
});

test('a field named action cannot replace the claim form submission URL', async () => {
    const {context, handlers} = runtime();
    const message = {setAttribute() {}};
    const form = {action: {toString() { return '[object HTMLSelectElement]'; }}, dataset: {},
        querySelector() { return message; }, getAttribute(name) { assert.equal(name, 'action'); return '/admin/order-workspace/71/action'; }};
    let requested; let reloaded = false;
    context.FormData = class { constructor(value) { assert.equal(value, form); } };
    context.adminRequest = async url => { requested = url; return {status: true, message: 'Saved'}; };
    context.location.reload = () => { reloaded = true; };
    const button = {disabled: false};
    await handlers.submit[0]({target: {closest() { return form; }}, submitter: button, preventDefault() {}});
    assert.equal(requested, '/admin/order-workspace/71/action');
    assert.equal(reloaded, true); assert.equal(button.disabled, false);
    assert.equal(form.dataset.submitting, undefined);
});
