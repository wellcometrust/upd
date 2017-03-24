const beautifyHTML = require('js-beautify').html

const indentSize = 4

const next = (str, stack) => {
  return stack.shift()(str, stack)
}

const optionalClosingTagFix = (str, stack) => {
  const openingComment = 'Temporary closing tag for beautification'
  const closingComment = 'End temporary closing tag for beautification'
  const placeholderRegExp = /<!-- (\/[a-z]+) -->/gi

  str = str.replace(placeholderRegExp, `<!-- ${openingComment} --><$1><!-- ${closingComment} -->`)

  const closingTagRegExp = new RegExp(`\\s{${indentSize}}<!-- ${openingComment} -->\\s+?<(/[a-z]+)>\\s+?<!-- ${closingComment} -->`, 'g')

  str = next(str, stack)

  str = str.replace(closingTagRegExp, '<!-- $1 -->')

  return str
}

const whitespaceFix = (str, stack) => {
  const whitespaceRegExp = /\s+(<!-- Avoid whitespace between .+?\s+-->)\s+/gi

  str = next(str, stack)

  str = str.replace(whitespaceRegExp, '$1')

  return str
}

const indentationFix = (str, stack) => {
  const indentRegExp = new RegExp(`\\n {${indentSize}}`, 'g')
  const wrapperRegExp = /^(<div>\s+)|(\s+<\/div>)$/g

  // Wrap the string in a div to avoid JS-Beautify bugs
  str = `<div>${str}</div>`

  str = next(str, stack)

  str = str.replace(wrapperRegExp, '')
  str = str.replace(indentRegExp, '\n')

  return str
}

const jsBeautify = (str) => {
  return beautifyHTML(str, {
    indent_size: indentSize,
    preserve_newlines: true,
    max_preserve_newlines: 1,
    unformatted: [
      'a',
      'b',
      'em',
      'i',
      'span',
      'strong',
      'sub',
      'sup',
      'u',
      'strike'
    ]
  })
}

const beautify = (str) => {
  let stack = [
    whitespaceFix,
    optionalClosingTagFix,
    indentationFix,
    jsBeautify
  ]

  return next(str, stack)
}

module.exports = beautify
