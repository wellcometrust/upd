const select = (selector) => {
  return [].slice.call(document.querySelectorAll(selector))
}

export default select
