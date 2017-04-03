import ResizeSensor from 'css-element-queries/src/ResizeSensor'
import classie from 'desandro-classie'
import debounce from 'lodash-es/debounce'

import select from '../select'

const buildList = (navEl, contentEl) => {
  return select('a[href^="#"]', navEl).reduce((list, el) => {
    const heading = select(el.hash, contentEl)

    heading.forEach((heading) => {
      list.push({
        el,
        heading
      })
    })

    return list
  }, [])
}

class ScrollSpy {
  constructor (navEl, contentEl, headerEl) {
    this.activeClassName = 'is-active'
    this.contentEl = contentEl || document.documentElement
    this.headerEl = headerEl
    this.navEl = navEl

    this.list = buildList(this.navEl, this.contentEl)

    if (!this.list.length) return

    this.activeEl = this.list[0].el

    ResizeSensor(document.documentElement, debounce(this.update.bind(this), 50))
    window.addEventListener('scroll', debounce(this.update.bind(this), 50))
  }

  _getOffset () {
    const offset = (this.headerEl)
      ? this.headerEl.getBoundingClientRect().bottom
      : 0

    return Math.max(0, offset)
  }

  update () {
    let activeEl = this.list[0].el
    let headerOffset = this._getOffset()

    for (let i = 0; i < this.list.length; i++) {
      let item = this.list[i]
      let bounds = item.heading.getBoundingClientRect()

      if (bounds.top < headerOffset) {
        activeEl = item.el
      } else {
        break
      }
    }

    if (activeEl !== this.activeEl) {
      classie.remove(this.activeEl, this.activeClassName)
      classie.add(activeEl, this.activeClassName)
      this.activeEl = activeEl
    }
  }
}

const initScrollSpies = (els) => {
  els = els || select('[data-scroll-spy]')

  const headerEl = select.first('.site-header')

  return els.map((el) => {
    const contentSelector = el.getAttribute('data-scroll-spy')

    const contentEl = select.first(contentSelector)

    return new ScrollSpy(el, contentEl, headerEl)
  })
}

export { initScrollSpies }

export default ScrollSpy
