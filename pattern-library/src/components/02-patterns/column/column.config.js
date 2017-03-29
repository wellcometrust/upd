const config = {
  context: {
    items: [
      'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi non viverra arcu. Integer nibh ante, laoreet a augue sed, tristique luctus sem. In porttitor elementum tellus, eu tincidunt lacus molestie id. Nulla egestas in augue id pharetra. Morbi et dolor elementum, ornare quam et, eleifend turpis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Etiam et erat in dolor tempus venenatis et pretium erat. Fusce ullamcorper pretium enim ac commodo. Curabitur sodales nisl vitae scelerisque sagittis. Etiam vitae massa in neque mollis suscipit egestas at metus. Morbi lorem urna, posuere in eros ut, convallis tempor dui. Cras pretium ante convallis, viverra turpis et, vestibulum mi. Etiam quis justo ex.',
      'Praesent laoreet lacus a tellus posuere, quis congue risus mattis. Curabitur pretium arcu non tellus tristique accumsan placerat fermentum eros. Sed euismod odio a nisl lacinia pulvinar. Nam tristique justo ut viverra efficitur. Praesent vitae libero quam. Nam augue massa, consequat vel eleifend sit amet, maximus id elit.',
      'Suspendisse venenatis, orci at tincidunt interdum, tellus ex consectetur risus, non posuere purus urna non orci. Praesent consequat erat eget volutpat varius. Morbi bibendum dui ut ligula lacinia, ac ullamcorper risus luctus. Phasellus rutrum viverra nunc. In iaculis purus nec dui porta fringilla ut eu nisi. Morbi mattis, purus quis commodo sollicitudin, metus erat sodales dolor, aliquet vestibulum neque ligula eget mauris. Aliquam finibus bibendum ante, a faucibus ipsum dapibus ut. Suspendisse maximus fermentum mi a egestas. Etiam in dui non nulla lacinia euismod. Donec facilisis sem lacinia, feugiat odio vel, feugiat sapien. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Maecenas ultrices tellus magna. Aenean mollis tortor sed quam scelerisque malesuada.',
      'Sed tincidunt pellentesque nisl sit amet aliquam. Duis convallis pretium mi, vitae condimentum massa congue ac. Vivamus porta, erat eget pulvinar eleifend, nisi ligula aliquet libero, in tincidunt nibh ligula laoreet eros. Sed lectus lorem, euismod in lectus eget, condimentum elementum mauris. Nam varius libero vitae sollicitudin interdum. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Quisque lectus elit, elementum ac nibh vel, euismod porttitor urna.'
    ]
  },
  notes: '* The column count defaults to one on small screens and two on larger screens. Add the `max-three` modifier to allow three columns on very large screens.' +
         '* The `column__group` element is optional: it simply prevents the column from breaking within the group.\n' +
         '* Bottom margins can be wrapped across columns, creating unwanted vertical whitespace at the top of the column. To avoid this, ensure that the last element in the column has no bottom margin. If space is required between elements, use bottom padding instead.\n' +
         '* Applying the `column` class directly to a bullet or numbered list may cause problems with the left-hand padding on the list. Avoid this by wrapping the list in a `div` (or other unstyled element).',
  preview: '@preview-boxed',
  variants: [
    {
      context: {
        modifiers: [
          'max-three'
        ]
      },
      name: 'Three columns'
    }
  ]
}

module.exports = config
