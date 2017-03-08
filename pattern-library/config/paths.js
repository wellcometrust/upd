const path = require('path')

const resolve = path.resolve.bind(path, __dirname, '..')

const sourceRoot = resolve('src')

const paths = {
  source: {
    components: resolve(sourceRoot, 'components'),
    docs: resolve(sourceRoot, 'docs'),
    root: sourceRoot
  }
}

module.exports = paths
