const FileManagerPlugin = require('filemanager-webpack-plugin');

module.exports = {
  mode: 'production',
  performance: { },
  entry: { },
  output: { },
  module: { },
  plugins: [
    new FileManagerPlugin({
      events: {
        onEnd: {
          delete: [
            './temp/cache'
          ],
        },
      },
    }),
  ]
};
