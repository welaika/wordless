devOptions = {
  mode: 'development'

  styleLoaders: [
    {
      loader: 'css-loader'
      options: {
        sourceMap: true
        minimize: false
      }
    }
    {
      loader: 'resolve-url-loader'
    }
    {
      loader: 'sass-loader'
      options: {
        sourceMap: true
      }
    }
  ]
  devtool: "source-map"
}

prodOptions = {
  mode: 'production'

  styleLoaders: [
    {
      loader: 'css-loader'
      options: {
        sourceMap: false
        minimize: true
      }
    }
    {
      loader: 'resolve-url-loader'
    }
    {
      loader: 'sass-loader'
      options: {
        sourceMap: false
      }
    }
  ]
  devtool: false
}

module.exports = (env) ->
  if env.WL_ENV is 'development'
    return devOptions

  return prodOptions
