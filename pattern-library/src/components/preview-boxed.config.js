const _ = require('lodash')

const sharedConfig = require('./preview.config')

const config = _.merge({}, sharedConfig, {
  context: {
    body: {
      class: 'box  box--horizontal'
    },
    main: {
      class: 'wrapper  wrapper--gutter'
    }
  }
})

module.exports = config
