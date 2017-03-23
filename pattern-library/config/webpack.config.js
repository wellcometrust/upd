const nodeExternals = require('webpack-node-externals')
const paths = require('./paths')

module.exports = {
  entry: paths.source.js,
  output: {
    filename: 'index.js',
    libraryTarget: 'umd',
    path: paths.dest.js
  },
  target: 'node',
  externals: [nodeExternals()],
  node: {
    __filename: true,
    __dirname: true
  },
  module: {
    rules: [
      {
        enforce: 'pre',
        exclude: /(node_modules|bower_components)/,
        loader: 'standard-loader',
        test: /\.jsx?$/
      },
      {
        exclude: /(node_modules|bower_components)/,
        test: /\.js$/,
        use: {
          loader: 'babel-loader'
        }
      }
    ]
  }
}
