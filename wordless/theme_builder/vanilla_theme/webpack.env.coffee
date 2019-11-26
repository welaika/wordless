MiniCssExtractPlugin = require('mini-css-extract-plugin')
fs = require('fs')

devOptions = {
  mode: 'development'
  styleLoaders: [
    {
      loader: MiniCssExtractPlugin.loader
      options: {
        sourceMap: true
      }
    }
    {
      loader: 'css-loader'
      options: {
        sourceMap: true
      }
    }
    {
      loader: 'sass-loader'
      options: {
        sourceMap: true
      }
    }
  ]
  devtool: "source-map",
  plugins: []
}

prodOptions = {
  mode: 'production'
  styleLoaders: [
    {
      loader: MiniCssExtractPlugin.loader
      options: {
        sourceMap: false
      }
    }
    {
      loader: 'css-loader'
      options: {
        sourceMap: false
      }
    }
    {
      loader: 'sass-loader'
      options: {
        sourceMap: false
      }
    }
  ]
  devtool: 'source-map'
  plugins: []
}

module.exports = (env) ->
  if env.NODE_ENV is 'production'
    return prodOptions
  else if env.NODE_ENV is 'development'
    return devOptions
