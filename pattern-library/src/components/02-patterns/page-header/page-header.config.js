const config = {
  context: {
    subheading: 'Mauris non tempor quam, et lacinia sapien. Mauris accumsan eros eget libero posuere vulputate. Etiam elit elit, elementum sed varius at.',
    title: 'Case studies'
  },
  variants: [
    {
      context: {
        category: 'Case study',
        modifiers: [
          'case-study'
        ],
        subheading: null,
        tags: true,
        title: 'ClinTouch – Helping people with psychosis'
      },
      name: 'Case study'
    },
    {
      context: {
        search: {
          value: 'Lorem ipsum'
        },
        subheading: null,
        title: 'Search results'
      },
      name: 'Search results'
    },
    {
      context: {
        highlightHeader: true,
        subheading: 'There is huge potential to make better use of information from people’s patient records. Data is vital for your individual care, and to improve health, care and services across the NHS.',
        title: 'Why is it important to use patient data?',
        image: true
      },
      name: 'Highlight page'
    }
  ]

}

module.exports = config
