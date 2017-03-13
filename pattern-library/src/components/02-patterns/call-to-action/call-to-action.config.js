const config = {
  collated: true,
  context: {
    icon: 'arrow',
    label: 'View case study'
  },
  default: 'Click-through',
  variants: [
    {
      context: {
        href: '#link-to-destination',
        icon: 'arrow',
        label: 'View case study'
      },
      name: 'Click-through (link)'
    },
    {
      context: {
        icon: 'arrow',
        label: 'View case study',
        small: true
      },
      name: 'Small click-through'
    },
    {
      context: {
        href: '#link-to-destination',
        icon: 'arrow',
        label: 'View case study',
        small: true
      },
      name: 'Small click-through (link)'
    }
  ]
}

module.exports = config