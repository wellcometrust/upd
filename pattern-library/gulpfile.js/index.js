const gulp = require('gulp')

const createSassTask = require('./sass')
const paths = require('../config/paths')

const isBuild = (process.env.CI === 'true' || process.env.NODE_ENV === 'build')

createSassTask(gulp, paths, isBuild)

let tasks = [
  'sass'
]

if (!isBuild) {
  tasks = tasks.concat([
    'sass:watch'
  ])
}

gulp.task('default', tasks)
