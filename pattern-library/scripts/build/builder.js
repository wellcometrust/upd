const fractal = require('../../fractal')

const builder = fractal.web.builder()

const logger = fractal.cli.console

builder.on('progress', (completed, total) => logger.update(`Exported ${completed} of ${total} items`, 'info'))

builder.on('error', err => {
  logger.error(err.message)
})

module.exports = builder
