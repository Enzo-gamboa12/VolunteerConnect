// Teste isolado do JavaScript NOVO de navegação, com DOM simulado; não é teste visual.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
function element() {
  const values = new Set();
  return {events:{},attributes:{},classList:{add:k=>values.add(k),remove:k=>values.delete(k),contains:k=>values.has(k),toggle:k=>{if(values.has(k)){values.delete(k);return false;}values.add(k);return true;}},
    addEventListener(event, callback){this.events[event]=callback;},setAttribute(key,value){this.attributes[key]=value;},focus(){this.focused=true;}};
}
const header=element(), button=element(), menu=element(), link=element(), document=element(), media=element();
header.querySelector=s=>s==='.vc-toggle'?button:menu;
header.querySelectorAll=()=>[link];
document.querySelector=()=>header;
vm.runInNewContext(fs.readFileSync(path.join(__dirname,'../app/js/navigation.js'),'utf8'), {document,window:{matchMedia:()=>media}});
assert(header.classList.contains('vc-ready'));
button.events.click();assert.equal(button.attributes['aria-expanded'],'true');assert(menu.classList.contains('vc-open'));
button.events.click();assert.equal(button.attributes['aria-expanded'],'false');
button.events.click();document.events.keydown({key:'Escape'});assert(!menu.classList.contains('vc-open'));assert(button.focused);
button.events.click();link.events.click();assert.equal(button.attributes['aria-expanded'],'false');
button.events.click();media.events.change();assert(!menu.classList.contains('vc-open'));
console.log('PASS: abrir/fechar menu, Escape, foco, link e mudança de largura — DOM simulado.');
