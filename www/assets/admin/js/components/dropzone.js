import Dropzone from 'dropzone';

// Ensure Dropzone does not auto discover elements
// that have the class 'dropzone' to avoid conflicts with manual initialization.
// This is useful if you want to control the initialization of Dropzone instances manually.
// If you want to use the default behavior, you can remove this line.
Dropzone.autoDiscover = false;

// Initialize Dropzone on the specified element
document.addEventListener('DOMContentLoaded', () => {
    const dropzoneElement = document.querySelector('#my-dropzone');

    if (dropzoneElement) {
        new Dropzone(dropzoneElement, {
            url: dropzoneElement.getAttribute('data-url'),
            paramName: 'file',
            // acceptedFiles: 'image/*,application/pdf',
            maxFilesize: 10, // Maximum file size in MB
            maxFiles: 5, // Maximum number of files
            addRemoveLinks: true, // Show remove links for files
            // thumbnailWidth: 150,
            // thumbnailHeight: 150,
            // autoProcessQueue: true,
            // parallelUploads: 2,

            init: function () {
                this.on('success', function (file, response) {
                    console.log('Uploaded:', response);
                });
                this.on('error', function (file, errorMessage) {
                    console.error('Upload error:', errorMessage);
                });
            }
        });
    }
});
