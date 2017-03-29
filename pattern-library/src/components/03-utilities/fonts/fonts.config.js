const config = {
  context: {
    name: 'body-copy'
  },
  default: 'Body copy',
  variants: [
    {
      context: {
        name: 'body-copy-small'
      },
      name: 'Small body copy'
    },
    {
      context: {
        name: 'intro'
      },
      name: 'Intro'
    }
  ]
}

module.exports = config
