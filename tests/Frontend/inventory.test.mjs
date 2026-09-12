import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { test } from 'node:test'
import { runInNewContext } from 'node:vm'

const blade = readFileSync(new URL('../../resources/views/supplier/rooms/setup/inventory.blade.php', import.meta.url), 'utf8')
const script = blade.match(/<script>([\s\S]*?)<\/script>/)[1]

function page(fetch) {
    const context = { fetch, URLSearchParams }
    runInNewContext(script, context)
    return context.inventoryGrid({ dataUrl: '/inventory', updateUrl: '/update', bulkUrl: '/bulk', previewUrl: '/preview', csrf: 'test-token' })
}

test('inventory query uses calendar month and shows load failure without stale dates', async () => {
    let requested
    const state = page(async url => {
        requested = url
        return { ok: false, json: async () => ({ message: 'Access denied.' }) }
    })
    state.month = new Date(2026, 8, 1)
    state.dates = ['2026-08-01']
    await state.load()
    assert.equal(requested, '/inventory?month=2026-09')
    assert.equal(state.error, 'Access denied.')
    assert.equal(state.dates.length, 0)
})

test('failed inventory save keeps the editor open and does not display an unsaved value', async () => {
    const state = page(async () => ({ ok: false, json: async () => ({ message: 'Invalid price.' }) }))
    state.cells = { '2026-09-15': { available: 3, price: 100 } }
    state.form = { available: 99, price: -1 }
    state.editing = '2026-09-15'
    await state.save('2026-09-15')
    assert.equal(state.cells['2026-09-15'].available, 3)
    assert.equal(state.error, 'Invalid price.')
    assert.equal(state.editing, '2026-09-15')
})

test('an old inventory response cannot replace a newer month', async () => {
    const pending = []
    const state = page(() => new Promise(resolve => pending.push(resolve)))
    const first = state.load()
    const second = state.load()
    pending[1]({ ok: true, json: async () => ({ label: 'New', dates: ['2026-10-01'], inventory: {}, defaults: { available: 3, price: 100 } }) })
    await second
    pending[0]({ ok: false, json: async () => ({ message: 'Old error' }) })
    await first
    assert.equal(state.label, 'New')
    assert.equal(state.error, null)
})

test('bulk save requires an unchanged preview and invalidates stale versions on conflict', async () => {
    let writes = 0
    const state = page(async (url, options) => {
        if (url.startsWith('/preview')) return { ok: true, json: async () => [{ date: '2026-10-01', version: 3, available: 2, price: 100 }] }
        writes++
        assert.equal(JSON.parse(options.body).rows[0].version, 3)
        return { ok: false, json: async () => ({ message: 'Inventory changed' }) }
    })
    state.bulk = { from: '2026-10-01', to: '2026-10-01', available: 3, price: 100 }
    await state.saveBulk()
    assert.equal(writes, 0)
    await state.previewBulk()
    state.bulk.price = 200
    await state.saveBulk()
    assert.equal(writes, 0)
    await state.previewBulk()
    await state.saveBulk()
    assert.equal(writes, 1)
    assert.equal(state.bulkRows.length, 0)
    assert.equal(state.error, 'Inventory changed')
})

test('single day submit sends the snapshot version and ignores a duplicate click', async () => {
    let finish, calls = 0
    const state = page((url, options) => {
        calls++
        assert.equal(JSON.parse(options.body).version, 7)
        return new Promise(resolve => { finish = resolve })
    })
    state.form = { version: 7, available: 2, price: 100 }
    const first = state.save('2026-10-01')
    await state.save('2026-10-01')
    assert.equal(calls, 1)
    finish({ ok: false, json: async () => ({ message: 'Conflict' }) })
    await first
    assert.equal(state.busy, false)
})
