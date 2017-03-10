const argv = require('yargs').argv
const path = require('path')

const resolve = path.resolve.bind(path, __dirname, '..')

const assetsFolder = resolve(argv.assetsFolder || 'temp')
const libraryFolder = argv.libraryFolder

const sourceRoot = resolve('src')

const paths = {
  dest: {
    assets: assetsFolder
  },
  source: {
    components: resolve(sourceRoot, 'components'),
    docs: resolve(sourceRoot, 'docs'),
    fonts: resolve(sourceRoot, 'fonts'),
    images: resolve(sourceRoot, 'images'),
    root: sourceRoot,
    scss: resolve(sourceRoot, 'scss')
  }
}

if (libraryFolder) paths.dest.library = resolve(libraryFolder)

module.exports = paths
