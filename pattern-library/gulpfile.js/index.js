const gulp = require('gulp')

const paths = require('../config/paths')

const isBuild = (process.env.CI === 'true' || process.env.NODE_ENV === 'build')

let tasks = [
  'icons',
  'sass',
  'static'
]

tasks.forEach((task) => {
  // Create the task
  require('./' + task)(gulp, paths, isBuild)

  // Add the `watch` version of the task to the task list
  if (!isBuild) tasks.push(task + ':watch')
})

gulp.task('default', tasks)

module.exports = gulp
