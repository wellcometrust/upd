const populate = (variant) => {
  const context = variant.context

  if (!context.label) {
    throw new Error('A label must be provided for the input')
  }

  if (!context.id) {
    context.id = context.label.toLowerCase().replace(/\W+/g, '-')
  }

  if (!context.name) {
    context.name = context.id
  }

  if (context.type === 'checkbox' || context.type === 'radio') {
    context.radiobox = true
  } else if (!context.type) {
    context.type = 'text'
  }

  if (!variant.name && !variant.default) variant.name = context.label

  return variant
}

let config = {
  collated: true,
  context: {
    label: 'Text input without placeholder'
  },
  variants: [
    {
      context: {
        label: 'Text input with placeholder',
        placeholder: 'Placeholder goes here'
      }
    },
    {
      context: {
        label: 'Email input without placeholder',
        type: 'email'
      }
    },
    {
      context: {
        label: 'Email input with placeholder',
        placeholder: 'mel.bloggs@example.com',
        type: 'email'
      }
    },
    {
      context: {
        label: 'Checkbox (initially unchecked)',
        name: 'checkboxes',
        type: 'checkbox'
      }
    },
    {
      context: {
        label: 'Checkbox (initially checked)',
        checked: true,
        name: 'checkboxes',
        type: 'checkbox'
      }
    },
    {
      context: {
        label: 'Radio button (initially unselected)',
        name: 'radio-buttons',
        type: 'radio'
      }
    },
    {
      context: {
        label: 'Radio button (initially selected)',
        checked: true,
        name: 'radio-buttons',
        type: 'radio'
      }
    },
    {
      context: {
        label: 'Select drop-down',
        options: [
          {
            label: 'Apple',
            value: 'apple'
          },
          {
            label: 'Banana',
            selected: true,
            value: 'banana'
          },
          {
            label: 'Orange',
            value: 'orange'
          }
        ]
      }
    }
  ]
}

config.default = config.context.label

// Populate fields on the default context
config = populate(config)

// Populate fields on the variants
config.variants = config.variants.map(populate)

module.exports = config
