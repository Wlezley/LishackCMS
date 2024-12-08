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
            './www/assets/admin/dist',
            './www/assets/tinymce-bundle/dist',
            './www/assets/website/dist'
          ],
        },
      },
    }),
  ]
};
