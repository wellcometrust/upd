const fractal = module.exports = require('@frctl/fractal').create()

const paths = require('./config/paths')

fractal.set('project.title', 'Understanding Patient Data')

// Assets
fractal.web.set('static.path', paths.dest.assets)

// Components
fractal.components.set('default.status', 'prototype')
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
