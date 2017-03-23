const changed = require('gulp-changed')
const path = require('path')

module.exports = (gulp, paths, isBuild) => {
  const staticSourceGlobs = [
    path.resolve(paths.source.fonts, '**', '*'),
    path.resolve(paths.source.images, '*'),
    path.resolve(paths.source.images, 'placeholder', '*')
  ]

  gulp.task('static', () => {
    return gulp.src(staticSourceGlobs, {base: paths.source.root})
      .pipe(changed(paths.dest.assets))
      .pipe(gulp.dest(paths.dest.assets))
  })

  gulp.task('static:watch', () => {
    gulp.watch(staticSourceGlobs, ['static'])
  })
}
