const config = {
  context: {
    cta: {
      icon: 'arrow',
      label: 'View case study',
      small: true
    },
    image: '/images/placeholder/case-study-tile-whooping-cough.jpg',
    level: '2',
    tag: 'Disease risks, causes & mechanisms',
    title: 'Whooping cough vaccination during pregnancy'
  },
  preview: '@preview-boxed',
  variants: [
    {
      context: {
        modifiers: ['alt']
      },
      name: 'Alternative background colour'
    }
  ]
}

module.exports = config
