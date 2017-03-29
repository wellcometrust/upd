const config = {
  context: {
    content: '<p>The box class adds padding to an element.</p>\n\n<p>It also removes the bottom margin from the last child.</p>'
  },
  preview: '@preview-boxed',
  variants: [
    {
      context: {
        content: '<p>When used with the <strong>highlight</strong> modifier, the background is set to the site&rsquo;s secondary background colour.</p>',
        modifiers: [
          'highlight'
        ]
      },
      name: 'Highlight'
    },
    {
      context: {
        content: '<p>When used with the <strong>large</strong> modifier, the padding around the box is slightly larger.</p>\n\n<p>This modifier will not work when used with the <strong>small</strong> modifier.</p>',
        modifiers: [
          'large'
        ]
      },
      name: 'Large'
    },
    {
      context: {
        content: '<p>When used with the <strong>small</strong> modifier, the padding around the box is slightly smaller.</p>',
        modifiers: [
          'small'
        ]
      },
      name: 'small'
    },
    {
      context: {
        content: '<p>When used with the <strong>horizontal</strong> modifier, the left- and right-hand side padding is removed.</p>',
        modifiers: [
          'horizontal'
        ]
      },
      name: 'Horizontal'
    },
    {
      context: {
        content: '<p>When used with the <strong>vertical</strong> modifier, the top and bottom padding is removed.</p>',
        modifiers: [
          'vertical'
        ]
      },
      name: 'Vertical'
    }
  ]
}

module.exports = config
