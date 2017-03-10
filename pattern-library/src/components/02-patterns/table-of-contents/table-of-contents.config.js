const titles = [
  'Introduction',
  'Why was this work needed?',
  'What happened?',
  'What were the benefits?',
  'What type of data was involved?',
  'What was the legal basis for accessing the data?',
  'Who funded and collaborated on this work?',
  'Where can I go for more information?'
]

const config = {
  context: {
    items: titles.map((title, index) => {
      let item = {
        href: '#' + title.toLowerCase()
          .replace(/\W+/g, '-').replace(/^-|-$/g, ''),
        title
      }

      if (index === 0) item.active = true

      return item
    })
  }
}

module.exports = config
