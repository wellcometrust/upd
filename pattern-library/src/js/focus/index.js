import select from '../select'

class Focus {
  constructor (el) {
    this.el = el

    const targetSelector = this.el.getAttribute('data-focus')

    this.targetEl = select.first(targetSelector)

    if (!this.targetEl) return

    this.el.addEventListener('click', this.focus.bind(this))
    this.el.addEventListener('toggle', this.focus.bind(this))
  }

  focus (e) {
    if (e.type === 'toggle' && e.detail.isExpanded !== true) return

    this.targetEl.focus()
  }
}

const initFocus = () => {
  const els = select('[data-focus]')

  return els.map((el) => {
    return new Focus(el)
  })
}

export { initFocus }

export default Focus
