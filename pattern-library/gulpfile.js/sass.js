const autoprefixer = require('autoprefixer')
const cssImport = require('postcss-import')
const cssnano = require('cssnano')
const noop = require('gulp-util').noop
const path = require('path')
const postcss = require('gulp-postcss')
const rename = require('gulp-rename')
const sass = require('gulp-sass')
const sourcemaps = require('gulp-sourcemaps')

const postcssPlugins = [
  cssImport(),
  autoprefixer(),
  cssnano()
]

module.exports = (gulp, paths, isBuild) => {
  const sassSourceGlob = path.resolve(paths.source.scss, '**', '*.scss')

  gulp.task('sass', () => {
    return gulp.src(sassSourceGlob, {base: paths.source.root})
      .pipe(isBuild ? noop : sourcemaps.init())
      .pipe(sass().on('error', sass.logError))
      .pipe(postcss(postcssPlugins))
      .pipe(rename((path) => {
        path.dirname = path.dirname.replace(/^scss\\\//, 'css')
      }))
      .pipe(isBuild ? noop : sourcemaps.write())
      .pipe(gulp.dest(paths.dest.assets))
  })

  gulp.task('sass:watch', () => {
    gulp.watch(sassSourceGlob, ['sass'])
  })
}
