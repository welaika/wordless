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


  devtool: "source-map",

  module:
    rules: [
      {
        test: /\.coffee$/
        loader: "coffee-loader"
      }
      {
        test: /\.s(a|c)ss$/,
        use: ExtractTextPlugin.extract([
          "css-loader?sourceMap"
          "resolve-url-loader"
          "sass-loader?sourceMap"
        ])
      },
      {
        test: /\.css$/
        use: ["style-loader", "css-loader", "resolve-url-loader"]
      },
      {
        test: /\.(jpe?g|png|gif|svg)$/i
        use: [
          "file-loader",
          loader: "image-webpack-loader",
          options:
              bypassOnDebug: false
        ]
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
