const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

module.exports = {
  mode: 'production',
  performance: {
    hints: false,
    // maxEntrypointSize: 512000,
    // maxAssetSize: 512000
  },
  entry: {
    'scripts': './www/assets/tinymce-bundle/js/main.js',
    'styles': './www/assets/tinymce-bundle/scss/main.scss',
  },
  output: {
    filename: '[name].min.js',
    path: path.resolve(__dirname, 'www/assets/tinymce-bundle/dist'),
    clean: true
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
