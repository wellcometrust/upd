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
    ]
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
        ]
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
        ]
      },
      name: 'Case studies'
    }
  ]
}

module.exports = config
