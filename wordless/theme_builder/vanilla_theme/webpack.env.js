const MiniCssExtractPlugin = require('mini-css-extract-plugin');

devOptions = {
  mode: 'development',
  styleLoaders: [
    {
      loader: MiniCssExtractPlugin.loader,
      options: {
        sourceMap: true
      }
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
  devtool: "source-map",
  plugins: []
};

prodOptions = {
  mode: 'production',
  styleLoaders: [
    {
      loader: MiniCssExtractPlugin.loader,
      options: {
        sourceMap: false
      }
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
  devtool: 'source-map',
  plugins: []
};

module.exports = function(env) {
  if (env.NODE_ENV === 'production') {
    return prodOptions;
  } else if (env.NODE_ENV === 'development') {
    return devOptions;
  }
};
