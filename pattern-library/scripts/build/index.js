process.env.CI = 'true'

const builder = require('./builder')

require('./assets').then(() => builder.build())
