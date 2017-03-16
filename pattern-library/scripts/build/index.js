process.env.CI = 'true'

const paths = require('../../config/paths')
const assets = require('./assets')

if ('library' in paths.dest) {
  const builder = require('./builder')

  assets.then(() => builder.build())
}
