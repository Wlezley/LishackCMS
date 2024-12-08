import './langs/cs';
// import './langs/sk';
// import './langs/de';

import contentCss from '!!raw-loader!tinymce/skins/content/tinymce-5-dark/content.min.css';
import contentUiCss from '!!raw-loader!tinymce/skins/ui/tinymce-5-dark/content.min.css';

tinymce.init({
  license_key: 'gpl',
  promotion: false,
  skin: false,
  content_css: false,
  content_style: [contentCss, contentUiCss].join('\n'),
  selector: '.tinymce',
  language: 'cs',
  plugins: [
    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
    'preview', 'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
    'insertdatetime', 'media', 'table', 'wordcount'
  ],
  toolbar: 'undo redo | blocks | ' +
    'bold italic backcolor | alignleft aligncenter ' +
    'alignright alignjustify | bullist numlist outdent indent | ' +
    'removeformat | code | help',
  height: 500,
  menubar: true,
})
