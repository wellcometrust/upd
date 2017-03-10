/* Package individual SVG icons into a single file */
const path = require('path')
const rename = require('gulp-rename')
const svgo = require('gulp-svgo')
const svgstore = require('gulp-svgstore')

const options = {
  inlineSvg: true
}

module.exports = (gulp, paths, isBuild) => {
  const iconGlob = path.resolve(paths.source.images, 'icons', '*.svg')

  gulp.task('icons', () => {
    return gulp.src(iconGlob)
      .pipe(rename((path) => {
        path.basename = 'svg-icon-' + path.basename
      }))
      .pipe(svgo())
      .pipe(svgstore(options))
      .pipe(gulp.dest(path.resolve(paths.dest.assets, 'images')))
  })

  gulp.task('icons:watch', function () {
    gulp.watch(iconGlob, ['icons'])
  })
}
