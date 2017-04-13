var path = require('path');

var srcDir = path.resolve(__dirname, 'theme/assets');
var dstDir = path.resolve(__dirname, 'assets');
var javascriptsDstPath = path.join(dstDir, '/javascripts');
var stylesheetsDstPath = path.join(dstDir, '/stylesheets');

var BrowserSyncPlugin = require('browser-sync-webpack-plugin');
var ExtractTextPlugin = require('extract-text-webpack-plugin');

module.exports = {
  entry: path.join(srcDir, '/main.js'),

  output: {
    filename: 'application.js',
    path: javascriptsDstPath,
  },

  devtool: 'source-map',

  module: {
    loaders: [
      {
        test: /\.coffee$/,
        loader: 'coffee-loader',
      },
      { test: /\.s(a|c)ss$/, loader: ExtractTextPlugin.extract('css-loader?sourceMap!resolve-url-loader!sass-loader?sourceMap') },
      { test: /\.css$/, loaders: ['style-loader', 'css-loader', 'resolve-url-loader'] },
      {
        test: /\.(jpe?g|png|gif|svg)$/i,
        loaders: [
            'file-loader?hash=sha512&digest=hex&name=../images/[hash].[ext]',
            'image-webpack-loader?optimizationLevel=7&interlaced=false'
        ]
      }
    ],
  },

  plugins: [
    new BrowserSyncPlugin({
      host: '127.0.0.1',
      port: 3000,
      proxy: {
        target: 'http://127.0.0.1:8080',
      },
    }),
    new ExtractTextPlugin('../stylesheets/screen.css'),
  ],
};
