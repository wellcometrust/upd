const _ = require('lodash')

const newsConfig = require('../news-tile/news-tile.config')

const headlines = [
  'Missing NHS medical correspondence',
  'NHS data loss: 500 patients may have suffered serious harm in data scandal',
  'NHS misplaced half a million patient documents',
  'Pellentesque faucibus facilisis metus, ut faucibus purus volutpat nec'
]

let newsTiles = headlines.map((title) => {
  return {
    component: 'news-tile',
    context: _.merge({}, newsConfig.context, {
      title
    })
  }
})

newsTiles = newsTiles.concat(newsTiles, newsTiles)

const config = {
  context: {
    items: [
      {
        context: {
          button: 'Pulvinar orci',
          description: '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla rutrum, purus ut laoreet pulvinar, orci eros fermentum augue, ac mattis libero metus at nisi. Nunc eget pharetra lectus, eget tempor diam. Mauris tempus risus leo, eget blandit felis eleifend non. Sed est est, scelerisque et eleifend sed, egestas hendrerit augue.</p>\n\n<p>Proin diam ligula, dictum at sapien quis, luctus sagittis eros. Donec nec semper lacus, egestas congue nisi. Aliquam et ultrices enim. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. In ut convallis tortor, sed placerat ipsum. Proin nunc lectus, convallis vitae magna vel, tempor vehicula libero. Integer viverra tristique finibus. In pulvinar orci odio, ac convallis leo tincidunt id. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>',
          title: 'Maecenas facilisis convallis urna'
        },
        component: 'resource-tile'
      },
      {
        component: 'resource-tile'
      },
      {
        context: {
          button: 'Case studies',
          description: '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla rutrum, purus ut laoreet pulvinar, orci eros fermentum augue, ac mattis libero metus at nisi. Nunc eget pharetra lectus, eget tempor diam. Mauris tempus risus leo, eget blandit felis eleifend non. Sed est est, scelerisque et eleifend sed, egestas hendrerit augue.</p>',
          title: 'Report on safeguards'
        },
        component: 'resource-tile'
      },
      {
        context: {
          button: 'Mauris vehicula',
          description: '<p>Maecenas rhoncus nisl sit amet nunc mollis molestie. Donec molestie ligula leo, sed semper leo vestibulum pretium. Mauris vehicula et diam sed laoreet. Nam faucibus blandit augue eget pellentesque. Etiam ligula massa, varius ut scelerisque vitae, congue at nunc.</p>',
          title: 'Nam faucibus blandit augue eget'
        },
        component: 'resource-tile'
      },
      {
        context: {
          button: 'Fermentum augue',
          description: '<p>Fusce lobortis sit amet eros in posuere. Morbi quis metus vehicula, rutrum neque in, tincidunt elit. Nulla convallis lacinia nisl quis consectetur. Phasellus dolor diam, ultrices a cursus vitae, maximus mattis eros. Etiam scelerisque, eros eu volutpat accumsan, odio nulla vulputate massa, vel ultricies nulla purus sed lectus. Nam eu nisl odio. Aliquam vel quam sodales, pharetra quam vitae, tempus arcu. Morbi pharetra nisi id nunc interdum fringilla.</p>',
          title: 'Volutpat accumsan, odio nulla vulputate massa'
        },
        component: 'resource-tile'
      },
      {
        context: {
          button: 'Orci blandit nisl',
          description: '<p>Curabitur non tortor sodales nulla sagittis vehicula vitae ac felis. Ut lacinia urna eu cursus tristique. Donec leo velit, interdum nec lacus in, scelerisque porttitor orci. Praesent hendrerit accumsan augue, sed mollis mauris gravida in. Integer dignissim vulputate tempus. In ullamcorper congue ultrices. Suspendisse eu justo leo. Vestibulum leo arcu, placerat eget sapien non, convallis ultrices nibh. Etiam sollicitudin, risus eu placerat interdum, magna nisl vulputate orci, eget feugiat ex orci blandit nisl. Integer posuere augue vel interdum luctus.</p>',
          title: 'In ullamcorper congue ultrices'
        },
        component: 'resource-tile'
      }
    ],
    widths: 'u-width-1-of-1  u-width-1-of-2-from-large'
  },
  preview: '@preview-boxed',
  variants: [
    {
      context: {
        items: newsTiles,
        widths: 'u-width-1-of-1  u-width-1-of-2-from-medium  u-width-1-of-3-from-x-large'
      },
      name: 'News'
    },
    {
      context: {
        items: [
          '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla rutrum, purus ut laoreet pulvinar, orci eros fermentum augue, ac mattis libero metus at nisi. Nunc eget pharetra lectus, eget tempor diam. Mauris tempus risus leo, eget blandit felis eleifend non. Sed est est, scelerisque et eleifend sed, egestas hendrerit augue.</p>',
          '<p>Proin diam ligula, dictum at sapien quis, luctus sagittis eros. Donec nec semper lacus, egestas congue nisi. Aliquam et ultrices enim. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. In ut convallis tortor, sed placerat ipsum. Proin nunc lectus, convallis vitae magna vel, tempor vehicula libero. Integer viverra tristique finibus. In pulvinar orci odio, ac convallis leo tincidunt id. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>',
          '<p>Maecenas facilisis convallis urna, at convallis lacus tincidunt iaculis. Donec et sem nunc. Sed molestie odio placerat condimentum ultricies. Donec ullamcorper dapibus lectus, in scelerisque enim gravida sit amet. Suspendisse lacinia ornare eros, vel cursus diam mattis ac. Mauris sit amet vulputate nisi. Praesent luctus finibus odio. Maecenas ut quam eu nulla tempus elementum a nec augue. Morbi vitae nunc imperdiet nisi efficitur tempor non vel dolor. Integer venenatis posuere leo, nec semper odio convallis sit amet. Praesent ut magna sit amet massa pharetra tempus. Nullam porttitor pellentesque mauris.</p>',
          '<p>Fusce lobortis sit amet eros in posuere. Morbi quis metus vehicula, rutrum neque in, tincidunt elit. Nulla convallis lacinia nisl quis consectetur. Phasellus dolor diam, ultrices a cursus vitae, maximus mattis eros. Etiam scelerisque, eros eu volutpat accumsan, odio nulla vulputate massa, vel ultricies nulla purus sed lectus. Nam eu nisl odio. Aliquam vel quam sodales, pharetra quam vitae, tempus arcu. Morbi pharetra nisi id nunc interdum fringilla.</p>',
          '<p>Quisque sollicitudin egestas nulla. Praesent id porttitor nibh. Etiam dolor sem, posuere pretium diam nec, auctor scelerisque turpis. Suspendisse imperdiet sem ac mollis rutrum. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque pulvinar laoreet purus non malesuada. Nulla facilisi.</p>',
          '<p>Curabitur non tortor sodales nulla sagittis vehicula vitae ac felis. Ut lacinia urna eu cursus tristique. Donec leo velit, interdum nec lacus in, scelerisque porttitor orci. Praesent hendrerit accumsan augue, sed mollis mauris gravida in. Integer dignissim vulputate tempus. In ullamcorper congue ultrices. Suspendisse eu justo leo. Vestibulum leo arcu, placerat eget sapien non, convallis ultrices nibh. Etiam sollicitudin, risus eu placerat interdum, magna nisl vulputate orci, eget feugiat ex orci blandit nisl. Integer posuere augue vel interdum luctus.</p>',
          '<p>Maecenas rhoncus nisl sit amet nunc mollis molestie. Donec molestie ligula leo, sed semper leo vestibulum pretium. Mauris vehicula et diam sed laoreet. Nam faucibus blandit augue eget pellentesque. Etiam ligula massa, varius ut scelerisque vitae, congue at nunc.</p>'
        ]
      },
      name: 'Plain text'
    }
  ]
}

module.exports = config
