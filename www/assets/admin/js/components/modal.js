import naja from 'naja';
import { Modal } from 'bootstrap';


export class AdminModal {
  constructor(modalSelector, dataSelector) {
    this.modalWindow = document.querySelector(modalSelector);
    this.bootstrapModal = new Modal(this.modalWindow);
    this.dataSelector = dataSelector;
    this.handle; // = function(params) {};
  }

  init(handle) {
    if (handle) {
      this.handle = handle;
    }

    this.modalWindow.addEventListener('show.bs.modal', function (event) {
      const sourceButton = event.relatedTarget;
      const data = JSON.parse(sourceButton.getAttribute(this.dataSelector));

      console.log('MODAL DATA', data);

      // Modal Elements
      const modalTitle = this.modalWindow.querySelector('.modal-title');
      const modalBody = this.modalWindow.querySelector('.modal-body');

      // Modal Action Button (cloneNode prevents the creation of duplicate events)
      const oldActionButton = this.modalWindow.querySelector('[data-modal-action]');
      const modalActionButton = oldActionButton.cloneNode(true);
      oldActionButton.parentNode.replaceChild(modalActionButton, oldActionButton);

      const actionName = modalActionButton.getAttribute('data-modal-action'); // Action name
      const bsModal = Modal.getOrCreateInstance(this.modalWindow); // Instance of Bootstrap Modal

      // Modal Content
      if (data.messages[actionName].title !== 'undefined') {
        modalTitle.textContent = data.messages[actionName].title;
      }
      if (data.messages[actionName].msg !== 'undefined') {
        modalBody.innerHTML = data.messages[actionName].msg;
      }

      // Action button click event with Naja request
      modalActionButton.addEventListener('click', function(event) {
        bsModal.hide();
        naja.makeRequest('POST', '?do=' + actionName, data).then(response => {
          if (response && response.status === 'error') {
            console.error('[Modal] Error: ' + response.message, data);
          } else {

            // TODO: Check if handle is instance of class or function, etc...
            if (handle && response.call) {
              this.handle[response.call](response.id);
            }
          }
        });
      }.bind(this));

    }.bind(this));
  }
}
