const select = (selector, container) => {
  container = container || document

  try {
    return [].slice.call(container.querySelectorAll(selector))
  } catch (err) {
    // Fail silently
    return []
  }
}

select.first = (selector, container) => {
  container = container || document

  try {
    return container.querySelector(selector)
  } catch (err) {
    // Fail silently
    return null
  }
}

export default select
