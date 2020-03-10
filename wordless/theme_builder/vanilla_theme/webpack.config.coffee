path = require 'path'
glob = require 'glob'

srcDir = path.resolve(__dirname, 'src')
dstDir = path.resolve(__dirname, 'dist')
javascriptsDstPath = path.join(dstDir, '/javascripts')
stylesheetsDstPath = path.join(dstDir, '/stylesheets')

BrowserSyncPlugin = require('browser-sync-webpack-plugin')
MiniCssExtractPlugin = require('mini-css-extract-plugin')
ImageminPlugin = require("imagemin-webpack-plugin").default
CopyWebpackPlugin = require('copy-webpack-plugin')

entries = ['main']

module.exports = (env) ->
  envOptions = require('./webpack.env')(env)

  return {
    mode: envOptions.mode

    entry: entries.reduce (object, current) ->
            object[current] = path.join(srcDir, "#{current}.js")
            return object
          , {}

    output: {
      filename: "[name].js"
      path: javascriptsDstPath
    }

    devtool: envOptions.devtool,

    module: {
      rules: [
        {
          test: /\.coffee$/
          use: [
            {
              loader: "coffee-loader",
              options: {
                transpile: {
                  presets: [[
                    '@babel/preset-env',
                    {
                      'useBuiltIns': 'usage'
                      'corejs': 3
                    }
                  ]]
                }
              }
            }
          ]
        }
        {
          test: /\.(sa|sc|c)ss$/
          use: envOptions.styleLoaders
        }
        {
          test: /\.css$/
          use: ["style-loader", "css-loader"]
        }
        {
          test: /\.(jpe?g|png|gif|svg)$/i
          use: [
            {
              loader: 'file-loader'
              options: {
                publicPath: (url, resourcePath, context) ->
                  relative = path.relative(context, resourcePath).split('/')
                  relative.shift()
                  return path.join(
                    '/wp-content/themes',
                    path.basename(__dirname),
                    'dist',
                    ...relative
                  )
                name: '[name].[ext]'
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
        files: [
          './views/**/*.pug',
          './views/**/*.php'
          './helpers/**/*.php',
        ]
      }

      new MiniCssExtractPlugin {
        filename: '../stylesheets/[name].css'
      }

      new ImageminPlugin {
        test: /\.(jpe?g|png|gif|svg)$/i
      }

      new CopyWebpackPlugin(
        [
          {
            from: path.join(srcDir, '/images'),
            to: path.join(dstDir, '/images', '[path][name].[ext]')
            toType: 'template'
          }
        ]
      )
    ].concat(envOptions.plugins)

    optimization: {
      splitChunks: {
        cacheGroups: {
          commons: {
            name: 'commons',
            chunks: 'all',
            minChunks: 2
          }
        }
      }
    }

    stats: 'normal'

    externals: {
      jquery: 'jQuery'
    }
  }
