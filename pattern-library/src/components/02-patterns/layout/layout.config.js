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
    },
    {
      context: {
        content: '<p>Contact the team directly on <a class="link-bare u-whitespace-nowrap" href="tel:+4420786118646">+44 207 8611 8646</a></p>\n\n<p>or email <a href="mailto:hello@understandingpatientdata.org.uk">hello@understandingpatientdata.org.uk</a></p>'
      },
      name: 'Contact us'
    }
  ]
}

module.exports = config
