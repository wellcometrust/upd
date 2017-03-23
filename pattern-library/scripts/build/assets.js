const webpack = require('webpack')

const config = require('../../config/webpack.config')
const gulp = require('../../gulpfile.js')

const name = 'default'

const gulpAssets = () => {
  return new Promise((resolve, reject) => {
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
}

const webpackAssets = () => {
  return new Promise((resolve, reject) => {
    console.log('Compiling JavaScript...')
    webpack(config).run((err, stats) => {
      if (err) {
        reject(err)
        return
      }

      if (stats.compilation.errors.length) {
        reject(stats.compilation.errors)
        return
      }

      if (stats.compilation.warnings.length) {
        console.info('There were some problems with JavaScript compilation.', stats.compilation.warnings)
      } else {
        console.log('JavaScript compiled successfully.')
      }

      resolve()
    })
  })
}

module.exports = gulpAssets().then(() => webpackAssets())
