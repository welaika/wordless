const path = require('path');

const srcDir = path.join(__dirname, '/theme/assets');
const dstDir = path.join(__dirname, '/assets');
const javascriptsDstPath = path.join(dstDir, '/javascripts');
const stylesheetsDstPath = path.join(dstDir, '/stylesheets');

const BrowserSyncPlugin = require('browser-sync-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

module.exports = {
  entry: path.join(srcDir, '/main.js'),

  output: {
    filename: 'application.js',
    path: javascriptsDstPath,
  },

  module: {
    loaders: [
      {
        test: /\.coffee$/,
        loader: 'coffee-loader',
      },
      {
        test: /\.s(a|c)ss$/,
        loader: ExtractTextPlugin.extract({
          fallbackLoader: 'style-loader',
          loader: 'css-loader?sourceMap!sass-loader?sourceMap',
        }),
      },
    ],
  },

  plugins: [
    new BrowserSyncPlugin({
      host: 'localhost',
      port: 3000,
      proxy: {
        target: 'http://127.0.0.1:8080',
      },
    }),
    new ExtractTextPlugin({ filename: stylesheetsDstPath + '/screen.css', disable: false, allChunks: true }),
  ],
};
