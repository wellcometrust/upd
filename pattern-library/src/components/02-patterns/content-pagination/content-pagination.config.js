const next = {
  icon: 'chevron-right',
  next: true,
  title: 'Tackling preventable amputations'
}

const previous = {
  icon: 'chevron-left',
  next: false,
  title: 'Whooping cough vaccination'
}

const config = {
  context: {
    items: [
      previous,
      next
    ]
  },
  variants: [
    {
      context: {
        items: [
          next
        ]
      },
      name: 'Next only'
    },
    {
      context: {
        items: [
          previous
        ]
      },
      name: 'Previous only'
    }
  ]
}

module.exports = config
