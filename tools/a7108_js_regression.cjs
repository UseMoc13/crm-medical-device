/**
 * A7.10.8: Node-only, database-free runtime regression for 3 role-hidden dialogs.
 * Usage: node tools/a7108_js_regression.js
 */
'use strict';
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.resolve(__dirname, '..');
const targets = [
  ['resources/views/deliveries/show.blade.php', 'deliveryConfirm', 'deliveryNextStatus', 'deliveryCancel'],
  ['resources/views/installations/show.blade.php', 'installationStatusModal', 'installationNextStatus', 'installationModalCancel'],
  ['resources/views/maintenance/_workflow.blade.php', 'mtStatusModal', 'mtNextStatus', 'mtStatusCancel'],
];
const element = () => ({hidden: true, value:'', dataset:{}, classList:{toggle(){}}, addEventListener(){}, querySelector(){return element();}, requestSubmit(){},focus(){}});
let checks = 0;
for(const [filename, modalId, inputId, cancelId] of targets){
  const src = fs.readFileSync(path.join(root, filename), 'utf8');
  const scripts = Array.from(src.matchAll(/<script(?:\s[^>]*)?>([\s\S]*?)<\/script\s*>/gi), m => m[1]);
  const snippet = scripts.find(s => s.includes(`document.getElementById('${modalId}')`));
  if(!snippet) throw Error(`Workflow inline script not found: ${filename}`);
  new vm.Script(snippet, {filename});
  checks++;
  for(const visible of [false,true]){
    const found = new Map();
    if(visible){ for(const id of [modalId, inputId, cancelId])found.set(id,element()); }
    else found.set(modalId,element()); // modal container still rendered; form is role-hidden
    const listeners = [];
    let checklistHandler;
    const button={addEventListener(event, callback){if(event==='click')checklistHandler=callback;}, closest(){return {querySelector(){return {value:''}},requestSubmit(){}}}, dataset:{completed:'1'}};
    const doc={getElementById(id){return found.get(id)|| (visible?element():null);},
      querySelectorAll(sel){return sel==='.checklist-toggle'&&filename.includes('installations/')?[button]:[]},
      addEventListener(ev,cb){listeners.push(ev)} };
    vm.runInNewContext(snippet,{document:doc, window:{setTimeout(){}},console}, {timeout:1000});
    checks++;
    if(filename.includes('installations/')&&typeof checklistHandler!=='function')throw Error('Installation checklist handler disappeared when status form is hidden');
  }
  console.log(`[PASS] ${filename} JS syntax + role-visible/hidden dialog`);
}
console.log(`[RESULT] A7108_JS_REGRESSION_PASS checks=${checks}`);
