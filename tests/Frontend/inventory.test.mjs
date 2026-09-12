import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { test } from 'node:test'
import { runInNewContext } from 'node:vm'

const blade = readFileSync(new URL('../../resources/views/supplier/rooms/setup/inventory.blade.php', import.meta.url), 'utf8')
const script = blade.match(/<script>([\s\S]*?)<\/script>/)[1]

function page(fetch) {
    const context = { fetch }
    runInNewContext(script, context)
    return context.inventoryGrid({ dataUrl: '/inventory', updateUrl: '/update', bulkUrl: '/bulk', csrf: 'test-token' })
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
