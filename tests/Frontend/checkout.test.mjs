import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { test } from 'node:test'
import { runInNewContext } from 'node:vm'

const blade = readFileSync(new URL('../../resources/views/booking/checkout.blade.php', import.meta.url), 'utf8')
const guard = blade.match(/@submit="([^"]+)"/)[1]

test('checkout blocks duplicate native form submission and retains the chosen outcome', () => {
    let prevented = 0
    const state = { submitting: false, outcome: 'success', $event: { preventDefault() { prevented++ } } }
    runInNewContext(guard, state)
    assert.equal(state.submitting, true)
    assert.equal(prevented, 0)
    runInNewContext(guard, state)
    assert.equal(prevented, 1)
    assert.equal(state.outcome, 'success')
    // A disabled submit button is excluded from native form data, so the command has its own hidden input.
    assert.match(blade, /type="hidden" name="outcome" :value="outcome"/)
})
