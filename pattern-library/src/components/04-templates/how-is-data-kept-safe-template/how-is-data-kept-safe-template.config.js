const config = {
  context: {
    content: [
      {
        name: 'intro',
        context: {
          content: '<p>It is essential that patient data is kept safe and secure, to protect patient confidentiality.</p>\n\n<p>There are four main ways that privacy is shielded.</p>'
        }
      },
      {
        name: 'content--how-is-data-kept-safe'
      }
    ],
    header: {
      title: 'How is data kept safe?'
    },
    noMobileSidebar: true,
    sidebar: [
      {
        name: 'table-of-contents',
        context: {
          items: [
            {
              active: true,
              href: '#content-1-identifying-information',
              title: '1. Identifying information is removed wherever possible'
            },
            {
              href: '#content-2-an-independent-review-process',
              title: '2. An independent review process'
            },
            {
              href: '#content-3-strict-legal-contracts',
              title: '3. Strict legal contracts'
            },
            {
              href: '#content-4-robust-data-security-standards',
              title: '4. Robust data security standards'
            }
          ]
        }
      }
    ],
    sticky: true
  }
}

module.exports = config
