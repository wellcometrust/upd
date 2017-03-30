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
    noMobileSidebar: true,
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
        noMobileSidebar: false,
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
        noMobileSidebar: false,
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
      noMobileSidebar: false,
      name: 'Contact us'
    },
    {
      context: {
        content: [
          '<div class="content    u-font-body-copy"><p>The Guardian is running a <a href="#link-to-story">story</a> today about 700,000 missing NHS letters.  We issued the following statement in response to the story:</p>\n\n<p>“Today’s news about the large amount of undelivered medical correspondence illustrates why the NHS can no longer rely on an outdated system of paper and faxes to correspond about patients.</p>\n\n<p>“If GPs and hospitals could communicate electronically, through a joined up and secure digital system, we would see a transformation in healthcare. It would allow the NHS to deliver a more effective service, protect patient confidentiality and, most importantly, significantly improve patient care.”</p></div>'
        ],
        sidebar: [
          {
            name: 'related-news'
          }
        ],
        noMobileSidebar: false,
        reverse: true
      },
      name: 'News article'
    }
  ]
}

module.exports = config
