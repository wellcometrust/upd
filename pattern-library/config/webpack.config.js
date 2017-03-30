const UglifyJSPlugin = require('uglifyjs-webpack-plugin')
const paths = require('./paths')
const webpack = require('webpack')

const isBuild = (process.env.CI === 'true' || process.env.NODE_ENV === 'build')

const plugins = []

if (isBuild) {
  plugins.push(new webpack.LoaderOptionsPlugin({
    minimize: true,
    debug: false
  }))
  plugins.push(new UglifyJSPlugin())
}

module.exports = {
  entry: paths.source.js,
  output: {
    filename: 'main.js',
    libraryTarget: 'umd',
    path: paths.dest.js
  },
  target: 'web',
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
        loader: 'babel-loader',
        options: {
          presets: [
            [
              'es2015',
              { modules: false }
            ]
          ]
        },
        test: /\.js$/
      }
    ]
  },
  plugins
}
