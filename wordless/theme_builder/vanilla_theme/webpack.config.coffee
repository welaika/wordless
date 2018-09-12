path = require 'path'
glob = require 'glob'

srcDir = path.resolve(__dirname, 'theme/assets')
dstDir = path.resolve(__dirname, 'assets')
javascriptsDstPath = path.join(dstDir, '/javascripts')
stylesheetsDstPath = path.join(dstDir, '/stylesheets')

BrowserSyncPlugin = require('browser-sync-webpack-plugin')
ExtractTextPlugin = require('extract-text-webpack-plugin')
ImageminPlugin = require("imagemin-webpack-plugin").default
CopyWebpackPlugin = require('copy-webpack-plugin')


module.exports = (env) ->
  envOptions = require('./webpack.env')(env)

  return {
    entry: path.join(srcDir, "/main.js")

    output: {
      filename: "application.js"
      path: javascriptsDstPath
    }

    mode: envOptions.mode

    devtool: envOptions.devtool

    module: {
      rules: [
        {
          test: /\.coffee$/
          use: "coffee-loader"
        }
        {
          test: /\.s(a|c)ss$/,
          use: ExtractTextPlugin.extract {
            use: envOptions.styleLoaders
          }
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
              options: {
                hash: 'sha512'
                digest: 'hex'
                name: '../images/[name]-[hash].[ext]'
              }
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
    }

    plugins: [
      new BrowserSyncPlugin {
          host: "127.0.0.1"
          port: 3000
          proxy: { target: "http://127.0.0.1:8080" }
          watchOptions: { ignoreInitial: true }
          files: [ './theme/views/**/*.pug' ]
        }

      new ExtractTextPlugin "../stylesheets/screen.css"

      new ImageminPlugin { test: /\.(jpe?g|png|gif|svg)$/i }

      new CopyWebpackPlugin(
        [
          {
            from: path.join(srcDir, '/images'),
            to: '../images/[name].[ext]'
            toType: 'template'
          }
        ]
      )
    ]
  }
