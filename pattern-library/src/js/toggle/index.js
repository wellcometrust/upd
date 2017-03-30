import CustomEvent from 'custom-event'
import classie from 'desandro-classie'

import select from '../select'

const emitToggle = (isExpanded) => {
  return new CustomEvent('toggle', {
    detail: {
      isExpanded
    }
  })
}

class Toggle {
  constructor (el) {
    this.el = el

    const targetId = this.el.getAttribute('aria-controls')

    this.className = this.el.getAttribute('data-toggle')
    this.groupId = this.el.getAttribute('data-toggle-group')
    this.targetEl = select.first('#' + targetId)

    if (!this.targetEl) return

    this.el.addEventListener('click', this.toggle.bind(this))
    this.targetEl.addEventListener('toggle', this._toggleSelf.bind(this))

    if (this._isTarget()) {
      this.toggle()
    }
  }

  _isTarget () {
    return '#' + this.el.id === window.location.hash || '#' + this.targetEl.id === window.location.hash
  }

  _toggleSelf (e) {
    const isExpanded = e.detail.isExpanded

    classie.toggle(this.el, this.className)
    this.el.setAttribute('aria-expanded', isExpanded)

    if (this._isTarget() && !isExpanded) {
      window.location.hash = '!'

      if (window.history && window.history.replaceState) {
        window.history.replaceState('', document.title, window.location.pathname + window.location.search)
      }
    }
  }

  toggle (e) {
    if (e) e.preventDefault()

    const isExpanded = (this.el.getAttribute('aria-expanded') !== 'true')

    if (isExpanded && this.groupId) {
      const activeEls = select(`[aria-expanded="true"][data-toggle-group="${this.groupId}"]`)

      activeEls.forEach((activeEl) => activeEl.click())
    }

    classie.toggle(this.targetEl, this.className)

    this.targetEl.dispatchEvent(emitToggle(isExpanded))
  }
}

const initToggles = () => {
  const triggerEls = select('[data-toggle]')

  return triggerEls.map((el) => {
    return new Toggle(el)
  })
}

export { initToggles }

export default Toggle
