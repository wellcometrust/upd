import select from '../select'

class HeaderSearch {
  constructor (submitEl, searchEl) {
    this.submitEl = submitEl
    this.searchEl = searchEl

    window.addEventListener('keyup', this._handleKeyup.bind(this))
    this.submitEl.addEventListener('click', this._handleSubmit.bind(this))
  }

  _handleKeyup (e) {
    if (e.keyCode === 27 && document.activeElement === this.searchEl) {
      this.searchEl.blur()
    }
  }

  _handleSubmit (e) {
    if (!this.searchEl.value) {
      e.preventDefault()

      this.searchEl.focus()
    }
  }
}

const initHeaderSearch = () => {
  const submitEl = select.first('.js-header-search')
  const searchEl = select.first('#header-search')

  if (!submitEl || !searchEl) return

  return new HeaderSearch(submitEl, searchEl)
}

export { initHeaderSearch }

export default HeaderSearch
