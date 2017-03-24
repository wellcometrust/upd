const fractal = module.exports = require('@frctl/fractal').create()
const nunjucks = require('@frctl/nunjucks')

const paths = require('../config/paths')
const beautify = require('./beautify')

fractal.set('project.title', 'Understanding Patient Data')

// Assets
fractal.web.set('static.path', paths.dest.assets)

// Builder
if (paths.dest.library) fractal.web.set('builder.dest', paths.dest.library)

// Components
fractal.components.engine(nunjucks)
fractal.components.set('default.status', 'prototype')
fractal.components.set('ext', '.nunj')
fractal.components.set('path', paths.source.components)

// Docs
fractal.docs.set('path', paths.source.docs)

// Theme
const mandelbrot = require('@frctl/mandelbrot')

const theme = mandelbrot({
  beautify,
  panels: [
    'html',
    'info',
    'notes'
  ],
  skin: 'white'
})

fractal.web.theme(theme)
