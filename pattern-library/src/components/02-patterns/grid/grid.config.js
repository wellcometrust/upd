const config = {
  context: {
    items: [
      'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi non viverra arcu. Integer nibh ante, laoreet a augue sed, tristique luctus sem. In porttitor elementum tellus, eu tincidunt lacus molestie id. Nulla egestas in augue id pharetra. Morbi et dolor elementum, ornare quam et, eleifend turpis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Etiam et erat in dolor tempus venenatis et pretium erat. Fusce ullamcorper pretium enim ac commodo. Curabitur sodales nisl vitae scelerisque sagittis. Etiam vitae massa in neque mollis suscipit egestas at metus. Morbi lorem urna, posuere in eros ut, convallis tempor dui. Cras pretium ante convallis, viverra turpis et, vestibulum mi. Etiam quis justo ex.',
      'Praesent laoreet lacus a tellus posuere, quis congue risus mattis. Curabitur pretium arcu non tellus tristique accumsan placerat fermentum eros. Sed euismod odio a nisl lacinia pulvinar. Nam tristique justo ut viverra efficitur. Praesent vitae libero quam. Nam augue massa, consequat vel eleifend sit amet, maximus id elit. Suspendisse quam diam, ullamcorper non tincidunt porttitor, molestie eget libero. Mauris vitae ultricies nisi. Sed maximus, mauris a pulvinar malesuada, elit orci varius ligula, vel ultricies purus sapien at ex. Phasellus pulvinar cursus neque, eget consectetur dolor pellentesque at. Vivamus condimentum massa vitae dapibus luctus. Duis bibendum condimentum lorem id imperdiet. Donec ultricies elementum augue, vitae varius est iaculis sed.',
      'Suspendisse venenatis, orci at tincidunt interdum, tellus ex consectetur risus, non posuere purus urna non orci. Praesent consequat erat eget volutpat varius. Morbi bibendum dui ut ligula lacinia, ac ullamcorper risus luctus. Phasellus rutrum viverra nunc. In iaculis purus nec dui porta fringilla ut eu nisi. Morbi mattis, purus quis commodo sollicitudin, metus erat sodales dolor, aliquet vestibulum neque ligula eget mauris. Aliquam finibus bibendum ante, a faucibus ipsum dapibus ut. Suspendisse maximus fermentum mi a egestas. Etiam in dui non nulla lacinia euismod. Donec facilisis sem lacinia, feugiat odio vel, feugiat sapien. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Maecenas ultrices tellus magna. Aenean mollis tortor sed quam scelerisque malesuada.',
      'Sed tincidunt pellentesque nisl sit amet aliquam. Duis convallis pretium mi, vitae condimentum massa congue ac. Vivamus porta, erat eget pulvinar eleifend, nisi ligula aliquet libero, in tincidunt nibh ligula laoreet eros. Sed lectus lorem, euismod in lectus eget, condimentum elementum mauris. Nam varius libero vitae sollicitudin interdum. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Quisque lectus elit, elementum ac nibh vel, euismod porttitor urna. Integer et mattis felis. Vivamus nisi ante, pulvinar et justo quis, efficitur dictum turpis. Vestibulum lacus nibh, aliquam eu est eget, interdum placerat enim. Aliquam bibendum cursus nisl, nec dignissim risus suscipit sit amet. Etiam eu turpis laoreet, dictum dui vulputate, interdum nunc. Cras volutpat, nulla id fermentum commodo, ipsum justo tristique leo, venenatis iaculis sem felis et purus.'
    ]
  },
  preview: '@preview-boxed',
  variants: [
    {
      context: {
        modifiers: [
          'center'
        ]
      },
      name: 'Centre-aligned'
    },
    {
      context: {
        gutter: 'small'
      },
      name: 'With small gutters'
    },
    {
      context: {
        gutter: 'medium'
      },
      name: 'With medium gutters'
    },
    {
      context: {
        gutter: 'large'
      },
      name: 'With large gutters'
    }
  ]
}

module.exports = config
