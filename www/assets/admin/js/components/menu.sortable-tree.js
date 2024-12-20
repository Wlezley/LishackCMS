import naja from 'naja';
import SortableTree, { SortableTreeNodeData } from 'sortable-tree';

export class MenuSettings {
  constructor(selector, style = 'sortable-tree') {
    this.selector = selector;
    this.element = document.querySelector(this.selector);
    this.style = style;
    this.nodes = [];
  }

  load() {
    naja.makeRequest('GET', '?do=load')
      .then(response => {
        this.nodes = response.nodes;
        this.render();
      })
      .catch(error => {
        console.error('Failed to load menu data:', error);
      });
  }

  // Options description: https://github.com/marcantondahmen/sortable-tree?tab=readme-ov-file#options
  render() {
    const tree = new SortableTree({
      nodes: this.nodes,
      element: this.element,
      icons: {
        collapsed: '<i class="fa-solid fa-circle-plus"></i>',
        open: '<i class="fa-solid fa-circle-minus"></i>',
      },
      styles: {
        tree: this.style,
        node: this.style + '__node',
        nodeHover: this.style + '__node--hover',
        nodeDragging: this.style + '__node--dragging',
        nodeDropBefore: this.style + '__node--drop-before',
        nodeDropInside: this.style + '__node--drop-inside',
        nodeDropAfter: this.style + '__node--drop-after',
        label: this.style + '__label',
        subnodes: this.style + '__subnodes',
        collapse: this.style + '__collapse',
      },
      stateId: this.selector,
      lockRootLevel: true,
      disableSorting: false,
      // disableSorting: (userRole !== 'admin'), // Example: Disable sorting based on user role.
      initCollapseLevel: 2,
      renderLabel: (data) => {
        var dataParsed = {
          id: data.id,
          name: data.name,
          name_url: data.name_url,
          url: data.url,
          hidden: data.hidden,
          messages: {
            delete: {
              title: 'Potvrzení o smazání',
              msg: 'Opravdu chcete menu <strong>' + data.name + '</strong> smazat?'
            }
          }
        };
        const editButton = `
          <a href="${adminUrl}/menu/edit?id=${data.id}" class="btn btn-primary" title="Upravit" aria-label="Upravit">
            <i class="fa-solid fa-pencil"></i>
          </a>`;
        const deleteButton = `
          <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteMenuConfirmModal" data-json='` + JSON.stringify(dataParsed) + `'>
            <i class="fa-solid fa-eraser"></i>
          </button>`;
        // TODO: Complete path
        const urlPath = data.id != 1 ? `<i id="st__menu-${data.id}" class="text-black-50 text-tiny fst-italic ms-2">(${data.name_url})</i>` : ``;
        return `
        <span class="pe-1" data-menu-id="${data.id}" data-menu-url="${data.name_url}">
          <span><i class="fa-solid fa-arrows-up-down-left-right text-black-50 pe-2"></i> [${data.id}] ${data.name} ${urlPath}</span>
          <span>
            ` + (data.id != 1 && userRole == 'admin' ? editButton : ``) + `
            ` + (data.id != 1 && userRole == 'admin' ? deleteButton : ``) + `
          </span>
        </span>
        `;
      },
      onChange: ({ nodes, movedNode, srcParentNode, targetParentNode }) => {
        const elementList = document.querySelectorAll("[data-menu-id]");
        const orderList = [];
        for (const [index, element] of elementList.entries()) {
          orderList.push(element.dataset.menuId);
        }
        const data = {
          node_id: movedNode.data.id,
          source_id: srcParentNode.data.id,
          target_id: targetParentNode.data.id,
          order_list: orderList,
        };
        this.save(data);
      },
      // confirm: (moved, parentNode) => {
      //   return true;
      // },
      // onClick: (event, node) => {
      //   console.log(event, node.data);
      // },
    });
  }

  save(data) {
    naja.makeRequest('POST', '?do=save', data).then(response => {
      if (response.status === 'success') {
        if (response.debug === true) {
          console.log('[MenuSettings]: ' + response.message);
        }
        if (response.nodes === undefined || response.nodes.length === 0) {
          console.error('[MenuSettings] Error: Unable to reload nodes');
        } else {
          this.nodes = response.nodes;
        }
      } else {
        console.error('[MenuSettings] Error: ' + response.message, data);
      }
    });
  }

  removeFromList(menuId) {
    console.log('Menu SortableTree -> removeFromList() ID', menuId); // DEBUG ONLY !!!

    var elementList = this.element.querySelectorAll(".sortable-tree__node");
    for (const el of elementList) {
      var inner = el.querySelector("[data-menu-id]");
      if (inner !== 'undefined' && inner.dataset.menuId == menuId) {
        el.remove();
      }
    }
  }
}
