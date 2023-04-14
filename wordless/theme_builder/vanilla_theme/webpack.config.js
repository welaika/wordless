const path = require('path');
const srcDir = path.resolve(__dirname, 'src');
const dstDir = path.resolve(__dirname, 'dist');
const javascriptsDstPath = path.join(dstDir, '/javascripts');
const _stylesheetsDstPath = path.join(dstDir, '/stylesheets');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const ImageMinimizerPlugin = require('image-minimizer-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');

const wordpressDir = path.resolve('../../../');
const imageFolderName = 'images'
const entries = ['main'];

module.exports = (env) => {
  let envOptions = require('./webpack.env')(env);

  return {
    mode: envOptions.mode,

    bail: envOptions.mode == 'production' ? true : false,

    entry: entries.reduce((object, current) => {
      object[current] = path.join(srcDir, `${current}.js`);
      return object;
    }, {}),

    output: {
      filename: "[name].js",
      path: javascriptsDstPath,
      publicPath: () => {
        return path.join(
          '/', // prepend slash to make an absolute web path
          path.relative(wordpressDir, dstDir) // dist folder relative to wordpress folder. Will be
                                              // something like `wp-content/theme/themeName/dist`
        )
      }
    },

    devtool: envOptions.devtool,

    module: {
      rules: [
        {
          test: /\.(js|es6)$/,
          exclude: /node_modules/,
          use: [
            {
              loader: "babel-loader",
              options: {
                presets: [
                  [
                    '@babel/preset-env',
                    {
                      'useBuiltIns': 'usage',
                      'corejs': 3
                    }
                  ]
                ]
              }
            }
          ]
        },
        {
          test: /\.(sa|sc|c)ss$/,
          use: envOptions.styleLoaders
        },
        {
          test: /\.css$/,
          use: ["style-loader", "css-loader"]
        },
        {
          test: /\.(jpe?g|png|gif|svg)$/i,
          type: 'asset/resource',
          generator: {
            filename: 'dist/images/[name][ext]'
          }
        }
      ]
    },

    plugins: [
      new BrowserSyncPlugin({
        host: "127.0.0.1",
        port: 3000,
        proxy: {
          target: "http://127.0.0.1:8080"
        },
        watchOptions: {
          ignoreInitial: true
        },
        files: [
          './views/**/*.pug',
          './views/**/*.php',
          './helpers/**/*.php'
        ]
      }),

      new MiniCssExtractPlugin({
        filename: '../stylesheets/[name].css'
      }),

      new CopyWebpackPlugin({
        patterns: [
          {
            from: path.join('**/*'),
            to: path.join(dstDir, imageFolderName, '[path]', '[name][ext]'),
            toType: 'template',
            context: path.join(srcDir, imageFolderName)
          }
        ],
      }),
    ].concat(envOptions.plugins),

    optimization: {
      splitChunks: {
        cacheGroups: {
          commons: {
            name: 'commons',
            chunks: 'all',
            minChunks: 2
          }
        }
      },
      minimizer: [
        new ImageMinimizerPlugin({
          minimizer: {
            implementation: ImageMinimizerPlugin.imageminMinify,
            options: {
              // Lossless optimization with custom option
              // Feel free to experiment with options for better result for you
              plugins: [
                ["gifsicle", { interlaced: true }],
                ["jpegtran", { progressive: true }],
                ["optipng", { optimizationLevel: 5 }],
                // Svgo configuration here https://github.com/svg/svgo#configuration
                [
                  "svgo",
                  {
                    plugins: [
                      {
                        name: "preset-default",
                        params: {
                          overrides: {
                            removeViewBox: false,
                            addAttributesToSVGElement: {
                              params: {
                                attributes: [
                                  { xmlns: "http://www.w3.org/2000/svg" },
                                ],
                              },
                            },
                          },
                        },
                      },
                    ],
                  },
                ],
              ],
            },
          },
        }),
      ]
    },

    stats: 'normal',

    externals: {
      jquery: 'jQuery'
    }
  };
};
