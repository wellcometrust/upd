const populateOptions = (category) => {
  category.options = category.options.map((label) => {
    return {
      id: category.name + '-' + label.toLowerCase().replace(/\W+/g, '-'),
      label
    }
  })

  return category
}

let categories = [
  {
    name: 'impact',
    options: [
      'Individual care',
      'Disearch risks and causes',
      'Diagnosis',
      'Treatment and prevention',
      'Planning',
      'Patient safety',
      'Policy'
    ],
    title: 'Impact of work'
  },
  {
    name: 'disease',
    options: [
      'Asthma',
      'Austism',
      'Cancer',
      'COPD',
      'Cardiovascular disease',
      'Dementia'
    ],
    title: 'Disease type'
  }
]

categories = categories.map(populateOptions)

const config = {
  collated: true,
  context: {
    search: '@search--small',
    type: 'div'
  },
  default: 'Keyword search',
  preview: '@preview-boxed',
  variants: [
    {
      context: {
        categories,
        search: false,
        submit: 'Apply filters',
        type: 'form'
      },
      name: 'Filters'
    }
  ]
}

module.exports = config
