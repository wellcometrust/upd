const fractal = module.exports = require('@frctl/fractal').create()

const paths = require('./config/paths')

fractal.set('project.title', 'Understanding Patient Data')

// Components
fractal.components.set('path', paths.source.components)

// Docs
fractal.docs.set('path', paths.source.docs)

// Theme
const mandelbrot = require('@frctl/mandelbrot')

const theme = mandelbrot({
  panels: [
    'html',
    'info',
    'notes'
  ],
  skin: 'white'
})

fractal.web.theme(theme)
