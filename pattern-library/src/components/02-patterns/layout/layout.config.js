const config = {
  context: {
    content: [
      {
        name: 'intro'
      },
      {
        name: 'content'
      }
    ],
    sidebar: [
      {
        name: 'table-of-contents'
      }
    ],
    sticky: true
  },
  default: 'Case study',
  preview: '@preview-boxed',
  variants: [
    {
      context: {
        content: [
          {
            name: 'search-results'
          }
        ],
        sidebar: [
          {
            name: 'refine--search-result-filters'
          }
        ],
        sticky: false
      },
      name: 'Search results'
    },
    {
      context: {
        content: [
          {
            name: 'search-results--case-studies'
          }
        ],
        sidebar: [
          {
            name: 'refine'
          }
        ],
        sticky: false
      },
      name: 'Case studies'
    }
  ]
}

module.exports = config
