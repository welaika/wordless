const MiniCssExtractPlugin = require('mini-css-extract-plugin');

const devOptions = {
  mode: 'development',
  styleLoaders: [
    {
      loader: MiniCssExtractPlugin.loader
    },
    {
      loader: 'css-loader',
      options: {
        sourceMap: true
      }
    },
    {
      loader: 'sass-loader',
      options: {
        sourceMap: true
      }
    }
  ],
  devtool: 'source-map',
  plugins: []
}

const prodOptions = {
  mode: 'production',
  styleLoaders: [
    {
      loader: MiniCssExtractPlugin.loader
    },
    {
      loader: 'css-loader',
      options: {
        sourceMap: false
      }
    },
    {
      loader: 'sass-loader',
      options: {
        sourceMap: false
      }
    }
  ],
  devtool: false,
  plugins: []
}

module.exports = function(env) {
  if (env.NODE_ENV === 'production') {
    return prodOptions;
  } else if (env.NODE_ENV === 'development') {
    env.DEBUG = 'true'
    return devOptions;
  }
};
