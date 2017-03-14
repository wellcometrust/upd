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
          'secondary'
        ]
      },
      name: 'Secondary'
    },
    {
      context: {
        modifiers: [
          'tertiary'
        ]
      },
      name: 'Tertiary'
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
