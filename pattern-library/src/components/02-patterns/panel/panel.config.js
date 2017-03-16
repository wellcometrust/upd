const config = {
  context: {
    items: [
      {
        type: 'section'
      }
    ]
  },
  variants: [
    {
      context: {
        items: [
          {
            type: 'section'
          },
          {
            type: 'aside'
          },
          {
            type: 'section'
          }
        ]
      },
      name: 'Multiple items'
    },
    {
      context: {
        items: [
          {
            type: 'section'
          }
        ],
        modifiers: [
          'alt'
        ]
      },
      name: 'Alternative background colour'
    },
    {
      context: {
        items: [
          {
            type: 'section'
          },
          {
            type: 'aside'
          },
          {
            type: 'section'
          }
        ],
        modifiers: [
          'alt'
        ]
      },
      name: 'Alternative background colour with multiple items'
    }
  ]
}

module.exports = config
