const _ = require('lodash')

const populate = (field) => {
  if (!field.label) {
    throw new Error('A label must be provided for the input')
  }

  if (!field.error) {
    field.error = false
  }

  if (!field.id) {
    field.id = field.label.toLowerCase().replace(/\W+/g, '-')
  }

  if (!field.name) {
    field.name = field.id
  }

  if (!field.type) {
    field.type = 'text'
  }

  return field
}

let context = {
  fields: [
    {
      label: 'Text input'
    },
    {
      label: 'Email input',
      type: 'email'
    },
    {
      label: 'Number input',
      pattern: '\\d',
      type: 'number'
    },
    {
      label: 'Password input',
      type: 'password'
    },
    {
      label: 'Select menu',
      options: [
        {
          label: 'Apple',
          'value': 'apple'
        },
        {
          label: 'Banana',
          'selected': true,
          'value': 'banana'
        },
        {
          label: 'Orange',
          disabled: true,
          'value': 'orange'
        }
      ],
      type: 'select'
    },
    {
      label: 'Telephone input',
      type: 'tel'
    },
    {
      label: 'Text area',
      type: 'textarea'
    }
  ],
  groups: [
    {
      fields: [
        {
          id: 'first-checkbox',
          label: 'First item',
          name: 'checkboxes',
          type: 'checkbox'
        },
        {
          checked: true,
          id: 'second-checkbox',
          label: 'Second item',
          name: 'checkboxes',
          type: 'checkbox'
        },
        {
          checked: true,
          id: 'third-checkbox',
          label: 'Third item',
          name: 'checkboxes',
          type: 'checkbox'
        }
      ],
      label: 'Group of checkboxes'
    },
    {
      fields: [
        {
          id: 'first-radio',
          label: 'First item',
          name: 'checkboxes',
          type: 'radio'
        },
        {
          checked: true,
          id: 'second-radio',
          label: 'Second item',
          name: 'checkboxes',
          type: 'radio'
        },
        {
          id: 'third-radio',
          label: 'Third item',
          name: 'checkboxes',
          type: 'radio'
        }
      ],
      label: 'Group of radio buttons'
    }
  ],
  submit: 'Submit'
}

context.fields = context.fields.map(populate)

const errorContext = _.merge({}, context)

const errorItems = [
  'fields',
  'groups'
]

errorItems.forEach((property) => {
  let errorMsg = 'Meaningful error message goes here.'

  errorContext[property].forEach((item) => {
    item.error = errorMsg
  })
})

const contactContext = {
  fields: [
    {
      label: 'Name',
      type: 'text'
    },
    {
      label: 'Email address',
      type: 'email'
    },
    {
      label: 'Phone',
      type: 'tel'
    },
    {
      label: 'Message',
      type: 'textarea'
    }
  ],
  groups: [],
  submit: 'Send'
}

contactContext.fields = contactContext.fields.map(populate)

const config = {
  context,
  preview: '@preview-boxed',
  variants: [
    {
      context: errorContext,
      name: 'With errors'
    },
    {
      context: contactContext,
      name: 'Contact us'
    }
  ]
}

module.exports = config
