const fs = require('fs')
const path = require('path')

const paths = require('../../config/paths')

const svgIcons = new Promise((resolve, reject) => {
  let filePath = path.resolve(paths.dest.assets, 'images', 'icons.svg')

  fs.readFile(filePath, (err, data) => {
    if (err) {
      reject(err)
      return
    }

    resolve(data)
  })
})

const config = {
  context: {
    svgIcons
  }
}

module.exports = config
