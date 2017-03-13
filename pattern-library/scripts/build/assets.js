const gulp = require('../../gulpfile.js')

const name = 'default'

const assets = new Promise((resolve, reject) => {
  gulp.on('task_err', (e) => {
    reject(e)
  })

  gulp.on('task_start', (e) => {
    console.log(`Starting ${e.task}...`)
  })

  gulp.on('task_stop', (e) => {
    console.log(`Task '${e.task}' complete!`)

    if (e.task === name) resolve(e)
  })

  gulp.start(name)
})

module.exports = assets
