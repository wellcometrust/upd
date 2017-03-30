const makeItem = (isCaseStudy) => {
  return {
    description: (isCaseStudy)
      ? 'Cras quis nulla commodo, aliquam lectus sed, blandit augue. Cras ullamcorper bibendum bibendum. Duis tincidunt urna non pretium porta. Nam condimentum vitae ligula vel ornare. Phasellus at semper turpis…'
      : '…estibulum rutrum quam vitae <strong>iaculis</strong> fringilla tincidunt. Suspendisse nec tortor urna. Ut laoreet sodales nisi, quis <strong>iaculis</strong> nulla <strong>iaculis</strong> vitae. Donec sagittis faucibus lacus eget blandit. Mauris vitae ultricies metus, at condimentum nulla. Donec quis ornare lacus. Etiam gravida mollis <strong>iaculis</strong> tortor quis porttitor…',
    title: 'Fusce vehicula dolor arcu, sit amet blandit'
  }
}

const makeItems = (count, isCaseStudy) => {
  let items = []

  for (let i = 0; i < count; i++) {
    let item = makeItem(isCaseStudy)

    if (isCaseStudy) {
      item.tags = true
    } else {
      item.category = 'Content type'
    }

    items.push(item)
  }

  return items
}

const config = {
  context: {
    count: 123,
    items: makeItems(10)
  },
  preview: '@preview-boxed',
  variants: [
    {
      context: {
        count: false,
        featured: true,
        items: makeItems(7, true)
      },
      name: 'Case studies',
      notes: 'The featured items should be repeated in the main list results (but with the `u-display-none-from-x-large` class) so that they are still visible on smaller devices.'
    }
  ]
}

module.exports = config
