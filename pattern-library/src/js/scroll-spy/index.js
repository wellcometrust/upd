import { ResizeSensor } from 'css-element-queries'
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
  constructor (navEl, contentEl) {
    this.activeClassName = 'is-active'
    this.contentEl = contentEl || document.documentElement
    this.navEl = navEl

    this.list = buildList(this.navEl, this.contentEl)

    if (!this.list.length) return

    this.activeEl = this.list[0].el

    ResizeSensor(document.documentElement, debounce(this.update.bind(this), 50))
    window.addEventListener('scroll', debounce(this.update.bind(this), 50))
  }

  update () {
    let activeEl = this.list[0].el

    for (let i = 0; i < this.list.length; i++) {
      let item = this.list[i]
      let bounds = item.heading.getBoundingClientRect()

      if (bounds.top < 1) {
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

  return els.map((el) => {
    const contentSelector = el.getAttribute('data-scroll-spy')

    const contentEl = select.first(contentSelector)

    return new ScrollSpy(el, contentEl)
  })
}

export { initScrollSpies }

export default ScrollSpy
