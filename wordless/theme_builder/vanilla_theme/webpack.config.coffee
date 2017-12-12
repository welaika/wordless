path = require 'path'
glob = require 'glob'

srcDir = path.resolve(__dirname, 'theme/assets')
dstDir = path.resolve(__dirname, 'assets')
javascriptsDstPath = path.join(dstDir, '/javascripts')
stylesheetsDstPath = path.join(dstDir, '/stylesheets')

BrowserSyncPlugin = require('browser-sync-webpack-plugin')
ExtractTextPlugin = require('extract-text-webpack-plugin')
ImageminPlugin = require("imagemin-webpack-plugin").default


module.exports =
  entry: path.join(srcDir, "/main.js")

  output:
    filename: "application.js"
    path: javascriptsDstPath


  devtool: "source-map"

  module:
    rules: [
      {
        test: /\.coffee$/
        use: "coffee-loader"
      }
      {
        test: /\.s(a|c)ss$/,
        use: ExtractTextPlugin.extract
          use: [
            {
              loader: 'css-loader'
              options:
                sourceMap: true
                minimize: true
            }
            {
              loader: 'resolve-url-loader'
            }
            {
              loader: 'sass-loader'
              options:
                sourceMap: true
            }
          ]
      }
      {
        test: /\.css$/
        use: ["style-loader", "css-loader", "resolve-url-loader"]
      }
      {
        test: /\.(jpe?g|png|gif)$/i
        use: [
          {
            loader: 'file-loader'
            options:
              hash: 'sha512'
              digest: 'hex'
              name: '../images/[name]-[hash].[ext]'
          }
        ]
      }
      {
        test: /\.woff(2)?(\?v=[0-9]\.[0-9]\.[0-9])?$/
        use: "url-loader"
      }
      {
        test: /\.(ttf|eot|svg)(\?v=[0-9]\.[0-9]\.[0-9])?$/
        use: "url-loader"
      }
    ]

  plugins: [
    new BrowserSyncPlugin
      host: "127.0.0.1"
      port: 3000
      proxy:
        target: "http://127.0.0.1:8080"

    new ExtractTextPlugin "../stylesheets/screen.css"

    new ImageminPlugin
      test: /\.(jpe?g|png|gif|svg)$/i
  ]
