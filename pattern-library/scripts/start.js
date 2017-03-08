const fractal = require('../fractal')

const logger = fractal.cli.console

const isProduction = (process.env.NODE_ENV === 'production')

logger.debugMode(!isProduction)

const server = fractal.web.server({
  sync: !isProduction
})

server.on('error', err => logger.error(err.message))

server.start().then(() => {
  logger.success(`Fractal server is now running`)
})
