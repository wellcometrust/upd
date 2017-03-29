const config = {
  context: {
    content: [
      {
        name: 'intro',
        context: {
          content: '<p>Understanding Patient Data was set up in late 2016, for the purpose of developing tools and resources to improve conversations about how health information is and can be used. We think that responsible use of patient data has the potential to do great things to improve health and care, but that transparency, accountability and respect for confidentiality are vitally important for data use to be justifiable.</p>\n\n<p>You can read about our funding, support, governance and our aims on this page, or contact us for more information. Understanding Patient Data is led by a small core team, based at the Wellcome Trust offices in London, UK and was set up to run for two years.</p>'
        }
      },
      {
        name: 'content--about-us'
      }
    ],
    header: {
      title: 'About us'
    },
    sidebar: [
      {
        name: 'table-of-contents',
        context: {
          items: [
            {
              active: true,
              href: '#content-aims-and-objectives',
              title: 'Aims and objectives'
            },
            {
              href: '#content-meet-the-team',
              title: 'Meet the team'
            },
            {
              href: '#content-governance',
              title: 'Governance'
            },
            {
              href: '#content-funding',
              title: 'Funding'
            },
            {
              href: '#content-supporters',
              title: 'Supporters'
            }
          ]
        }
      }
    ],
    sticky: true
  }
}

module.exports = config
