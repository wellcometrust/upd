import classie from 'desandro-classie'
import Masonry from 'masonry-layout'

import select from '../select'

const masonry = (target) => {
  const masonry = new Masonry(target, {
    initLayout: false,
    itemSelector: '.masonry__item',
    percentPosition: true
  })

  masonry.once('layoutComplete', () => {
    classie.remove(target, 'masonry--loading')

    // Force relayout to correct vertical margin issue
    window.setTimeout(() => masonry.layout())
  })

  masonry.layout()

  return masonry
}

masonry.init = (els) => {
  els = els || select('.masonry')

  return els.map(masonry)
}

export default masonry
