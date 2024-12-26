import naja from 'naja';
import { Modal } from 'bootstrap';


export class AdminModal {
  constructor(modalSelector, dataSelector) {
    this.modalWindow = document.querySelector(modalSelector);
    this.bootstrapModal = new Modal(this.modalWindow);
    this.dataSelector = dataSelector;
    this.handle;
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
      if (data.modal) {
        if (data.modal.title !== 'undefined') {
          modalTitle.textContent = data.modal.title;
        }
        if (data.modal.body !== 'undefined') {
          modalBody.innerHTML = data.modal.body;
        }
      }

      // Action button click event with Naja request
      modalActionButton.addEventListener('click', function(event) {
        bsModal.hide();
        naja.makeRequest('POST', '?do=' + actionName, data).then(response => {
          if (response && response.status === 'error') {
            console.error('[Modal] Error: ' + response.message, data);
          } else {
            if (handle && response.call) {
              // Call the handle Class.function()
              this.handle[response.call](response.id);
            }
          }
        });
      }.bind(this));
    }.bind(this));
  }

  initStatic() {
    this.modalWindow.addEventListener('show.bs.modal', function (event) {
      const sourceButton = event.relatedTarget;

      if (this.dataSelector) {
        const data = JSON.parse(sourceButton.getAttribute(this.dataSelector));

        console.log('MODAL DATA', data);

        // Modal Content
        if (data && data.modal) {
          if (data.modal.title !== 'undefined') {
            const modalTitle = this.modalWindow.querySelector('.modal-title');
            modalTitle.textContent = data.modal.title;
          }
          if (data.modal.body !== 'undefined') {
            const modalBody = this.modalWindow.querySelector('.modal-body');
            modalBody.innerHTML = data.modal.body;
          }
        }
      }
    }.bind(this));
  }
}
