const config = {
  context: {
    title: 'What you need to know',
    standfirst: 'Everyone should be able to find out how patient data is used and why, what the safeguards are, and how decisions are made.',
    link: 'Watch video'
  },
  default: 'Static image full width',
  variants: [
    {
      context: {
        fullWidth: false
      },
      name: 'Static image non full width'
    },
    {
      context: {
        video: true,
        fullWidth: true
      },
      name: 'Looping Video full width'
    },
    {
      context: {
        video: true
      },
      name: 'Looping Video non full width'
    }
  ]

}

module.exports = config
