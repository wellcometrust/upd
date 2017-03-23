import classie from 'desandro-classie'

import select from '../select'

const toggle = (el) => {
  const className = el.getAttribute('data-toggle')
  const isExpanded = el.getAttribute('aria-expanded') === 'true'
  const targetId = el.getAttribute('aria-controls')

  const targetEl = select('#' + targetId)
  const triggerEls = select(`[aria-controls="${targetId}"][data-toggle]`)

  targetEl.forEach((el) => classie.toggle(el, className))

  triggerEls.forEach((el) => {
    classie.toggle(el, className)
    el.setAttribute('aria-expanded', !isExpanded)

    if ('#' + el.id === window.location.hash && isExpanded) {
      window.location.hash = ''
    }
  })
}

toggle.init = () => {
  const triggerEls = select('[data-toggle]')

  triggerEls.forEach((el) => {
    if ('#' + el.id === window.location.hash) {
      toggle(el)
    }

    el.addEventListener('click', (e) => {
      e.preventDefault()

      toggle(el)
    })
  })
}

export default toggle
