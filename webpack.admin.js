const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

module.exports = {
  mode: 'production',
  // mode: 'development',
  // devtool: 'inline-source-map',
  performance: {
    hints: false,
    // maxEntrypointSize: 512000,
    // maxAssetSize: 512000
  },
  entry: {
    'scripts': './www/assets/admin/js/main.js',
    'styles': './www/assets/admin/scss/main.scss'
  },
  output: {
    filename: '[name].min.js',
    path: path.resolve(__dirname, 'www/assets/admin/dist'),
    clean: true,
    library: 'LishackCMS',
    libraryTarget: 'umd',
  },
  module: {
    rules: [
      {
        test: /\.scss$/i,
        exclude: /node_modules/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader
          },
          {
            loader: 'css-loader',
            options: {
              url: false
            }
          },
          {
            loader: 'postcss-loader',
            options: {
              postcssOptions: {
                plugins: function () {
                  return [
                    require('autoprefixer')
                  ];
                }
              }
            }
          },
          {
            loader: 'sass-loader',
            options: {
              sassOptions: {
                quietDeps: true,
                silenceDeprecations: ['import'],
                sourceMap: false
              }
            }
          }
        ],
      },
      {
        test: /\.js?$/i,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
        }
      }
    ]
  },
  plugins: [
    new webpack.ProvidePlugin({
      $: "jquery",
      "window.jQuery": "jquery",
      jQuery: "jquery"
    }),
    new MiniCssExtractPlugin({
      filename: '[name].css',
      chunkFilename: '[name].css'
    }),
    new CleanWebpackPlugin({
      cleanStaleWebpackAssets: false,
      cleanOnceBeforeBuildPatterns: ['scripts.min.js', 'styles.css'],
      protectWebpackAssets: false,
      cleanAfterEveryBuildPatterns: ['styles.min.js']
    })
  ]
};
