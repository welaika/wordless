const path = require('path');

const srcDir = path.join(__dirname, '/theme/assets');
const dstDir = path.join(__dirname, '/assets');
const javascriptDstPath = path.join(dstDir, '/javascripts');

const BrowserSyncPlugin = require('browser-sync-webpack-plugin');

module.exports = {
  entry: path.join(srcDir, '/main.js'),

  output: {
    filename: 'application.js',
    path: javascriptDstPath,
  },

  module: {
    loaders: [
      {
        test: /\.coffee$/,
        loader: 'coffee-loader',
      },
      {
        test: /\.s(a|c)ss$/,
        loaders: ['style-loader', 'css-loader?sourceMap', 'sass-loader?sourceMap'],
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
  ],
};
