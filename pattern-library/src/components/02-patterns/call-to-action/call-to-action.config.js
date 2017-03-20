const config = {
  collated: true,
  context: {
    icon: 'arrow',
    label: 'View case study'
  },
  default: 'Click-through',
  variants: [
    {
      context: {
        href: '#link-to-destination',
        icon: 'arrow',
        label: 'View case study'
      },
      name: 'Click-through (link)'
    },
    {
      context: {
        icon: 'arrow',
        label: 'View case study',
        small: true
      },
      name: 'Small click-through'
    },
    {
      context: {
        href: '#link-to-destination',
        icon: 'arrow',
        label: 'View case study',
        small: true
      },
      name: 'Small click-through (link)'
    },
    {
      context: {
        href: '#link-to.docx',
        icon: false,
        label: 'Public support for research in the NHS',
        asset: true
      },
      name: 'Asset link (DOCX)'
    },
    {
      context: {
        href: '#link-to.pptx',
        icon: false,
        label: 'Public support for research in the NHS',
        asset: true
      },
      name: 'Asset link (PPTX)'
    },
    {
      context: {
        href: '#link-to.pdf',
        icon: false,
        label: 'Public support for research in the NHS',
        asset: true
      },
      name: 'Asset link (PDF)'
    }
  ]
}

module.exports = config
