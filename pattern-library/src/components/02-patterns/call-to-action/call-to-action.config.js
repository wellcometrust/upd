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
        icon: 'arrow',
        label: 'View case study',
        small: true
      },
      name: 'Small click-through'
    }
  ]
}

module.exports = config
