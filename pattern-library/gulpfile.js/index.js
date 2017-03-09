const gulp = require('gulp')

const createSassTask = require('./sass')
const createStaticTask = require('./static')
const paths = require('../config/paths')

const isBuild = (process.env.CI === 'true' || process.env.NODE_ENV === 'build')

createSassTask(gulp, paths, isBuild)
createStaticTask(gulp, paths, isBuild)

let tasks = [
  'sass',
  'static'
]

if (!isBuild) {
  tasks = tasks.concat([
    'sass:watch',
    'static:watch'
  ])
}

gulp.task('default', tasks)
