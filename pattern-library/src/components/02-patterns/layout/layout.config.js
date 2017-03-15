const config = {
  default: 'Case study',
  preview: '@preview-boxed',
  variants: [
    {
      context: {
        refine: '@refine--search-result-filters',
        'search-results': '@search-results'
      },
      name: 'Search results'
    },
    {
      context: {
        refine: '@refine',
        'search-results': '@search-results--case-studies'
      },
      name: 'Case studies'
    }
  ]
}

module.exports = config
