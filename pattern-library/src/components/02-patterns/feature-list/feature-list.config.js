const config = {
  context: {
    news: true,
    title: 'Latest news'
  },
  default: 'Latest news',
  preview: '@preview-boxed',
  variants: [
    {
      context: {
        news: false,
        title: 'Case studies'
      },
      name: 'Case studies'
    }
  ]
}

module.exports = config
