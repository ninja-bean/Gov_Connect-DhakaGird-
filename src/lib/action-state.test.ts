import { describe, expect, it } from 'vitest'
import { fieldErrors } from './action-state'

describe('fieldErrors', () => {
  it('promotes form-level errors under the _form key', () => {
    expect(
      fieldErrors({ fieldErrors: { email: ['Invalid'] }, formErrors: ['Generic'] }),
    ).toEqual({ _form: ['Generic'] })
  })

  it('returns field errors verbatim when there are no form errors', () => {
    expect(
      fieldErrors({ fieldErrors: { email: ['Invalid'], password: ['Too short'] }, formErrors: [] }),
    ).toEqual({ email: ['Invalid'], password: ['Too short'] })
  })

  it('returns an empty object when there are no errors', () => {
    expect(fieldErrors({ fieldErrors: {}, formErrors: [] })).toEqual({})
  })
})