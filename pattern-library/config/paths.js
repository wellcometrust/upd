const argv = require('yargs').argv
const path = require('path')

const resolve = path.resolve.bind(path, __dirname, '..')

const assetsFolder = resolve(argv.assetsFolder || 'temp')
const libraryFolder = argv.libraryFolder

const sourceRoot = resolve('src')

const paths = {
  dest: {
    assets: assetsFolder,
    js: resolve(assetsFolder, 'js')
  },
  resolve,
  source: {
    components: resolve(sourceRoot, 'components'),
    docs: resolve(sourceRoot, 'docs'),
    fonts: resolve(sourceRoot, 'fonts'),
    images: resolve(sourceRoot, 'images'),
    js: resolve(sourceRoot, 'js'),
    placeholderImages: resolve(sourceRoot, 'images', 'placeholder'),
    root: sourceRoot,
    scss: resolve(sourceRoot, 'scss')
  }
}

if (libraryFolder) {
  paths.dest.library = resolve(libraryFolder)

  paths.dest.placeholderImages = resolve(paths.dest.library, 'images', 'placeholder')
}

module.exports = paths
