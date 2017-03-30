const changed = require('gulp-changed')
const imagemin = require('gulp-imagemin')
const path = require('path')

module.exports = (gulp, paths, isBuild) => {
  const imageGlob = path.resolve(paths.source.images, '*.{jpg,gif,png,svg}')

  gulp.task('images', () => {
    return gulp.src(imageGlob, {base: paths.source.root})
      .pipe(changed(paths.dest.assets))
      .pipe(imagemin())
      .pipe(gulp.dest(paths.dest.assets))
  })

  gulp.task('images:watch', () => {
    gulp.watch(imageGlob, ['images'])
  })
}
