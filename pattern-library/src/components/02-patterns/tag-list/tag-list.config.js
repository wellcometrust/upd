let tags = [
  'Planning',
  'Disease risks and causes',
  'Diagnosis',
  'Patient safety',
  'Policy',
  'Treatment and prevention',
  'Individual care'
]

const populateTags = (tag) => {
  return {
    modifiers: [
      tag.toLowerCase().replace(/\W+/g, '-')
    ],
    tag
  }
}

tags = tags.map(populateTags)

const config = {
  collated: true,
  context: {
    tags
  }
}

module.exports = config
