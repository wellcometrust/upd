const icons = [
  'arrow',
  'chevron-left',
  'chevron-right',
  'hamburger',
  'search'
]

const blockIcons = icons.map((icon) => {
  return {
    icon,
    title: 'Title goes here'
  }
})

const inlineIcons = icons.map((icon) => {
  return {
    icon,
    inline: true
  }
})

const config = {
  collated: true,
  context: {
    icons: blockIcons
  },
  preview: '@preview-boxed',
  variants: [
    {
      context: {
        icons: inlineIcons
      },
      name: 'Inline (presentational)'
    }
  ]
}

module.exports = config
