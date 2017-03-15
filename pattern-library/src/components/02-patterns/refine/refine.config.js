const populateOptions = (category) => {
  category.options = category.options.map((label) => {
    return {
      id: category.name + '-' + label.toLowerCase().replace(/\W+/g, '-'),
      label
    }
  })

  return category
}

let caseStudyCategories = [
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

let searchResultCategories = [
  {
    name: 'content-type',
    options: [
      'News',
      'Case study',
      'Report',
      'Resource',
      'Presentation'
    ],
    title: 'Content type'
  }
]

caseStudyCategories = caseStudyCategories.map(populateOptions)
searchResultCategories = searchResultCategories.map(populateOptions)

const config = {
  context: {
    categories: caseStudyCategories,
    search: '@search--small',
    submit: 'Apply filters'
  },
  default: 'Case study filters',
  preview: '@preview-boxed',
  variants: [
    {
      context: {
        categories: searchResultCategories,
        search: false,
        submit: 'Apply filters'
      },
      name: 'Search result filters'
    }
  ]
}

module.exports = config
