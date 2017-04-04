import classie from 'desandro-classie'

import select from '../select'

class HeaderSearch {
  constructor (formEl, submitEl, searchEl) {
    this._timer = null
    this.activeClassName = 'is-active'
    this.formEl = formEl
    this.submitEl = submitEl
    this.searchEl = searchEl

    this.formEl.setAttribute('novalidate', true)

    window.addEventListener('keyup', this._handleKeyup.bind(this))

    this.formEl.addEventListener('mousedown', this._handleMousedown.bind(this))
    this.formEl.addEventListener('submit', this._handleSubmit.bind(this))

    this.searchEl.addEventListener('blur', this._handleBlur.bind(this))
    this.searchEl.addEventListener('focus', this._handleFocus.bind(this))

    this.submitEl.addEventListener('blur', this._handleBlur.bind(this))
    this.submitEl.addEventListener('click', this._handleClick.bind(this))
    this.submitEl.addEventListener('focus', this._handleFocus.bind(this))
  }

  _handleBlur (e) {
    // Use timeout to check focus, as blur may be fired on one element in the
    // form immediately before the next one is focused.
    this._timer = setTimeout(() => {
      if (!this._isFocused()) {
        classie.remove(this.formEl, this.activeClassName)
      }
    })
  }

  _handleClick (e) {
    if (!this._isFocused()) {
      this.searchEl.focus()
      e.preventDefault()
    }
  }

  _handleFocus (e) {
    clearTimeout(this._timer)

    if (!classie.has(this.formEl, this.activeClassName)) {
      this.searchEl.focus()
      classie.add(this.formEl, this.activeClassName)
    }
  }

  _handleKeyup (e) {
    if (e.keyCode === 27 && document.activeElement === this.searchEl) {
      classie.remove(this.formEl, this.activeClassName)
    }
  }

  _handleMousedown (e) {
    // Prevent blur event firing on mouse down
    e.preventDefault()
  }

  _handleSubmit (e) {
    if (!this.searchEl.value) {
      e.preventDefault()

      this.searchEl.focus()
    }
  }

  _isFocused () {
    return (document.activeElement.parentElement === this.formEl)
  }
}

const initHeaderSearch = () => {
  const formEl = select.first('.js-header-search')
  const submitEl = select.first('.js-header-search-submit', formEl)
  const searchEl = select.first('.js-header-search-input', formEl)

  if (!formEl || !submitEl || !searchEl) return

  return new HeaderSearch(formEl, submitEl, searchEl)
}

export { initHeaderSearch }

export default HeaderSearch
