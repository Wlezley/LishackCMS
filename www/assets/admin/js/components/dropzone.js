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

            // Custom preview template
            // previewTemplate: '<div class="dz-preview dz-file-preview"><div class="dz-image"><img data-dz-thumbnail /></div><div class="dz-details"><div class="dz-size"><span data-dz-size></span></div><div class="dz-filename"><span data-dz-name></span></div></div><div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div><div class="dz-error-message"><span data-dz-errormessage></span></div><div class="dz-success-mark"><svg viewBox="0 0 54 54"><path d="M45.9,13.2L20.1,39l-9.9-9.9L4.1,36l16.2,16.2L50.8,22.1L45.9,13.2z"/></svg></div><div class="dz-error-mark"><svg viewBox="0 0 54 54"><path d="M27,0C12.1,0,0,12.1,0,27s12.1,27,27,27s27-12.1,27-27S41.9,0,27,0z M37.7,37.7c-1.5,1.5-3.5,2.3-5.7,2.3 s-4.2-0.8-5.7-2.3l-10-10c-3-3-3-7.9,0-10l10-10c1.5-1.5,3.5-2.3,5.7-2.3s4.2,0.8,5.7,2.3c3,3,3,7.9,0,10L37.7,37.7z"/></svg></div></div>',
            // Custom preview element
            // previewElement: '<div class="dz-preview dz-file-preview"><div class="dz-image"><img data-dz-thumbnail /></div><div class="dz-details"><div class="dz-size"><span data-dz-size></span></div><div class="dz-filename"><span data-dz-name></span></div></div><div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div><div class="dz-error-message"><span data-dz-errormessage></span></div><div class="dz-success-mark"><svg viewBox="0 0 54 54"><path d="M45.9,13.2L20.1,39l-9.9-9.9L4.1,36l16.2,16.2L50.8,22.1L45.9,13.2z"/></svg></div><div class="dz-error-mark"><svg viewBox="0 0 54 54"><path d="M27,0C12.1,0,0,12.1,0,27s12.1,27,27,27s27-12.1,27-27S41.9,0,27,0z M37.7,37.7c-1.5,1.5-3.5,2.3-5.7,2.3 s-4.2-0.8-5.7-2.3l-10-10c-3-3-3-7.9,0-10l10-10c1.5-1.5,3.5-2.3,5.7-2.3s4.2,0.8,5.7,2.3c3,3,3,7.9,0,10L37.7,37.7z"/></svg></div></div>',
            // Custom upload progress element
            // uploadProgressElement: '<div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>',

            // dictDefaultMessage: 'Drag and drop files here or click to upload',
            // dictFallbackMessage: 'Your browser does not support drag and drop file uploads.',
            // dictInvalidFileType: 'This file type is not allowed.',
            // dictFileTooBig: 'File is too big ({{filesize}}MB). Max allowed size: {{maxFilesize}}MB.',
            // dictResponseError: 'Server responded with {{statusCode}} code.',
            // dictCancelUpload: 'Cancel upload',
            // dictUploadCanceled: 'Upload canceled.',
            // dictCancelUploadConfirmation: 'Are you sure you want to cancel this upload?',
            // dictRemoveFile: 'Remove file',
            // dictMaxFilesExceeded: 'You can only upload a maximum of {{maxFiles}} files.',

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
