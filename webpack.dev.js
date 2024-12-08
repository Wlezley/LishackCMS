const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const SVGSpritemapPlugin = require("svg-spritemap-webpack-plugin");
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

module.exports = {
  mode: 'development',
  performance: {
    hints: false,
    // maxEntrypointSize: 512000,
    // maxAssetSize: 512000
  },
  devtool: 'inline-source-map',
  entry: {
    'scripts': './www/assets/website/js/main.js',
    'styles-main': './www/assets/website/scss/main.scss',
    'styles-print': './www/assets/website/scss/print.scss'
  },
  output: {
    filename: '[name].min.js',
    path: path.resolve(__dirname, 'www/assets/website/dist'),
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
            loader: 'sass-loader',
            options: {
              sassOptions: {
                quietDeps: true,
                silenceDeprecations: ['import'],
                sourceMap: true
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
    new SVGSpritemapPlugin("www/assets/website/icons/**/*.svg", {
      output: {
        filename: "../images/icons.svg"
      },
      sprite: {
        prefix: false,
        generate: {
          title: false
        }
      }
    }),
    new MiniCssExtractPlugin({
      filename: '[name].css',
      chunkFilename: '[name].css'
    }),
    new CleanWebpackPlugin({
      cleanStaleWebpackAssets: false,
      cleanOnceBeforeBuildPatterns: ['scripts.min.js', 'styles-main.css', 'styles-print.css'],
      protectWebpackAssets: false,
      cleanAfterEveryBuildPatterns: ['styles-main.min.js', 'styles-print.min.js']
    })
  ]
};
