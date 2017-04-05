import ResizeSensor from 'css-element-queries/src/ResizeSensor'
import classie from 'desandro-classie'
import debounce from 'lodash-es/debounce'
import Masonry from 'masonry-layout'

import select from '../select'

const masonry = (target) => {
  const masonry = new Masonry(target, {
    initLayout: false,
    itemSelector: '.masonry__item',
    percentPosition: true
  })

  const debouncedLayout = debounce(masonry.layout.bind(masonry), 50)

  const masonryItems = masonry.getItemElements()

  // Trigger layout whenever a masonry item changes size
  masonryItems.forEach((el) => ResizeSensor(el, debouncedLayout))

  masonry.once('layoutComplete', () => {
    classie.remove(target, 'masonry--loading')
  })

  masonry.layout()

  return masonry
}

const initMasonries = (els) => {
  els = els || select('.masonry')

  return els.map(masonry)
}

export { initMasonries }

export default masonry
