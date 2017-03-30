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
        href: '#link-to-destination',
        icon: 'download',
        label: 'Download PPT'
      },
      name: 'Download (link)'
    },
    {
      context: {
        icon: 'download',
        label: 'Download PPT',
        small: true
      },
      name: 'Download'
    },
    {
      context: {
        asset: true,
        href: '#link-to.docx',
        icon: false,
        label: 'Public support for research in the NHS',
        small: true
      },
      name: 'Asset link (DOCX)'
    },
    {
      context: {
        asset: true,
        href: '#link-to.pptx',
        icon: false,
        label: 'Public support for research in the NHS',
        small: true
      },
      name: 'Asset link (PPTX)'
    },
    {
      context: {
        asset: true,
        href: '#link-to.pdf',
        icon: false,
        label: 'Public support for research in the NHS',
        small: true
      },
      name: 'Asset link (PDF)'
    }
  ]
}

module.exports = config
