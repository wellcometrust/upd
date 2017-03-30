const config = {
  variants: [
    {
      context: {
        home: true
      },
      name: 'Homepage'
    },
    {
      context: {
        popup: {
          id: 'popup-refine',
          label: 'Refine by'
        }
      },
      name: 'Search results'
    }
  ]
}

module.exports = config
