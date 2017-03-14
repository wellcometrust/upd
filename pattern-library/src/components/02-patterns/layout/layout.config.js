const config = {
  default: 'Case study',
  preview: '@preview-boxed',
  variants: [
    {
      context: {
        'search-results': '@search-results'
      },
      name: 'Search results'
    },
    {
      context: {
        'search-results': '@search-results--case-studies'
      },
      name: 'Case studies'
    }
  ]
}

module.exports = config
