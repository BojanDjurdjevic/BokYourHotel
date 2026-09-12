import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { test } from 'node:test'
import { runInNewContext } from 'node:vm'
const script = readFileSync(new URL('../../resources/views/hotels/_search.blade.php', import.meta.url), 'utf8').match(/<script>([\s\S]*?)<\/script>/)[1]
function page(fetch) { const context = { fetch }; runInNewContext(script,context); return context.destinationSearch('/destinations'); }
test('short queries do not fetch and an old response cannot replace new input', async () => {
    const pending=[]; const state=page(()=>new Promise(resolve=>pending.push(resolve)));
    state.term='p'; await state.search(); assert.equal(pending.length,0);
    state.term='pa'; const old=state.search();
    state.term='lo'; state.changed(); const current=state.search();
    pending[1]({ok:true,json:async()=>[{city:'London',country:'UK'}]}); await current;
    pending[0]({ok:true,json:async()=>[{city:'Paris',country:'France'}]}); await old;
    assert.equal(state.results[0].city,'London');
});
test('keyboard selects canonical city and country; escape dismisses pending requests', async () => {
    const state=page(async()=>({ok:true,json:async()=>[{city:'Paris',country:'France'},{city:'Palermo',country:'Italy'}]}));
    state.term='pa'; await state.search();
    const key=k=>state.key({key:k,preventDefault(){}});
    key('ArrowUp'); assert.equal(state.active,1); state.active=-1;
    key('ArrowDown'); assert.equal(state.active,0);
    key('ArrowDown'); key('ArrowUp'); key('Enter');
    assert.equal(state.city,'Paris'); assert.equal(state.country,'France'); assert.equal(state.open,false);
    state.term='pa'; state.changed(); await state.search(); key('Escape'); assert.equal(state.open,false);
});
test('empty results and load errors are visible without stale selections', async () => {
    const state=page(async()=>({ok:true,json:async()=>[]}));
    state.term='zz'; await state.search(); assert.equal(state.open,true); assert.equal(state.results.length,0);
    state.country='France'; state.term='Rome'; state.changed(); assert.equal(state.country,'');
    const failed=page(async()=>({ok:false,json:async()=>({message:'Slow down'})})); failed.term='pa'; await failed.search();
    assert.equal(failed.error,'Slow down'); assert.equal(failed.loading,false);
});
