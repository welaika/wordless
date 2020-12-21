const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = function(env) {
  if (env.NODE_ENV == 'development') { env.DEBUG = 'true' }
  const needSourceMap = (env.DEBUG == 'true');
  return {
    mode: env.NODE_ENV,
    styleLoaders: [
      {
        loader: MiniCssExtractPlugin.loader
      },
      {
        loader: 'css-loader',
        options: {
          sourceMap: needSourceMap
        }
      },
      {
        loader: 'sass-loader',
        options: {
          sourceMap: needSourceMap
        }
      }
    ],
    devtool: needSourceMap ? 'source-map' : false,
    plugins: []
  };
};
