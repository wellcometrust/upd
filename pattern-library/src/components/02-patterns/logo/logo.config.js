const config = {
  context: {
    home: true
  },
  default: 'Home page',
  variants: [
    {
      context: {
        home: false
      },
      name: 'Other pages'
    }
  ]
}

module.exports = config
