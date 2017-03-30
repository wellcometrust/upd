process.env.CI = 'true'

const fs = require('fs-extra')

const paths = require('../../config/paths')
const assets = require('./assets')

if ('library' in paths.dest) {
  const builder = require('./builder')

  assets.then(() => builder.build()).then(() => {
    // Copy placeholder images into the pattern library.
    // We don't do this as part of the asset build step, as we want to make
    // sure that they're not used in the theme.
    console.log('Copying placeholder images...')
    fs.copySync(paths.source.placeholderImages, paths.dest.placeholderImages)
    console.log('Placeholder images copied.')
  })
}
