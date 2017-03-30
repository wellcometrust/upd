const config = {
  context: {
    primaryPanel: {
      items: [
        {
          content: [
            {
              name: 'testimonial'
            }
          ],
          type: 'aside'
        },
        {
          content: [
            {
              name: 'feature-list'
            }
          ],
          type: 'section'
        }
      ],
      modifiers: [
        'alt'
      ]
    },
    secondaryPanel: {
      items: [
        {
          content: [
            {
              name: 'feature-list--case-studies'
            }
          ],
          type: 'section'
        }
      ]
    }
  }
}

module.exports = config
