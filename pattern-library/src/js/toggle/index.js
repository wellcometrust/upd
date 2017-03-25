import classie from 'desandro-classie'

import select from '../select'

const toggle = (el) => {
  const className = el.getAttribute('data-toggle')
  const groupId = el.getAttribute('data-toggle-group')
  const isExpanded = el.getAttribute('aria-expanded') === 'true'
  const targetId = el.getAttribute('aria-controls')

  const targetEl = select('#' + targetId)
  const triggerEls = select(`[aria-controls="${targetId}"][data-toggle]`)

  targetEl.forEach((el) => classie.toggle(el, className))

  triggerEls.forEach((el) => {
    // If the toggle has a group, toggle all other active elements in the group
    if (groupId) {
      const activeEls = select(`[data-toggle-group="${groupId}"].${className}`)

      activeEls.forEach((targetEl) => {
        if (targetEl !== el) {
          toggle(targetEl)
        }
      })
    }

    // Toggle the current element
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
