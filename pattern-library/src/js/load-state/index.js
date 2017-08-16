import classie from 'desandro-classie'

const setClassName = () => {
  const el = document.documentElement

  // Force Safari to calculate layout by querying document width, otherwise
  // the class name may be toggled before the document has been displayed
  el.offsetWidth  // eslint-disable-line no-unused-expressions

  classie.remove(el, 'preload')
  classie.add(el, 'postload')
}

const eventName = ('pageshow' in window)
  ? 'pageshow'
  : 'load'

window.addEventListener(eventName, setClassName)
