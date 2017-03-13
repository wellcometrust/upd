const config = {
  default: 'Standard',
  variants: [
    {
      context: {
        modifiers: [
          'shadow'
        ]
      },
      name: 'Shadow',
      notes: '* The shadow variant is only available in the primary colour'
    },
    {
      context: {
        modifiers: [
          'blue'
        ]
      },
      name: 'Blue'
    },
    {
      context: {
        modifiers: [
          'small'
        ]
      },
      name: 'Small'
    }
  ]
}

module.exports = config
