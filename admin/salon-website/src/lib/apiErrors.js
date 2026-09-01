export class ClientApiError extends Error {
  constructor(message, fieldErrors = null) {
    const messages = flattenValidationErrors(fieldErrors)
    const display = messages.length > 0 ? messages : [message || 'Request failed']
    super(display.join(' '))
    this.name = 'ClientApiError'
    this.fieldErrors = fieldErrors || {}
    this.messages = display
  }
}

export function flattenValidationErrors(errors) {
  if (!errors || typeof errors !== 'object') return []
  return Object.values(errors)
    .flat()
    .filter((msg) => typeof msg === 'string' && msg.trim() !== '')
}

export function fieldError(fieldErrors, key) {
  const list = fieldErrors?.[key]
  if (!list) return null
  return Array.isArray(list) ? list[0] : list
}
