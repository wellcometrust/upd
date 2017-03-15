const pages = []

const count = 5

for (let i = 0; i < count; i++) {
  pages.push({
    number: i + 1
  })
}

pages[2].active = true

const config = {
  context: {
    pages
  },
  preview: '@preview-boxed'
}

module.exports = config
