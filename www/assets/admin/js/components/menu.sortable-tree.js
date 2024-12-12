import naja from 'naja';
import SortableTree, { SortableTreeNodeData } from 'sortable-tree';

export class MenuSettings {
  constructor(selector, style = 'sortable-tree') {
    this.selector = selector;
    this.style = style;
    this.nodes = [];

    console.log(style);
  }

  // Load data through Naja.js
  load(url = '?do=load') {
    naja.makeRequest('GET', url)
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
    console.log('SortableTree INPUT', this.nodes);
    const tree = new SortableTree({
      nodes: this.nodes,
      element: document.querySelector(this.selector),
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
      // stateId: this.selector,
      lockRootLevel: true,
      disableSorting: false,
      initCollapseLevel: 2,
      renderLabel: (data) => {
        return `
        <span>
          <span><!-- [ID: ${data.id}] --> ${data.name} <i class="text-black-50 text-tiny fst-italic ms-2">(${data.name_url})</i></span>
          <span><i class="fa-solid fa-up-down"></i></span>
        </span>
        `;
      },
      onChange: ({ nodes, movedNode, srcParentNode, targetParentNode }) => {
        console.log('NODE', movedNode);
        // console.log('SOURCE', srcParentNode.data);
        // console.log('TARGET', targetParentNode.data);
        this.save(movedNode.data, srcParentNode.data, targetParentNode.data, nodes);
        movedNode.data.id = 0;
      },
      // confirm: (moved, parentNode) => {
      //   return true;
      // },
      // onClick: (event, node) => {
      //   console.log('NODE DATA (click)', node.data);
      // },
    });
  }

  // Save data through Naja.js
  save(node, source, target, nodes) {
    naja.makeRequest('POST', '?do=save', {
      node_id: node.id,
      source_id: source.id,
      target_id: target.id,
      nodes: nodes,
    }).then(response => {
      if (response.status === 'success') {
        if (response.debug === true) { console.log('[MenuSettings]: ' + response.message); }
      } else {
        console.error('[MenuSettings] Error: ' + response.message, { node, source, target });
      }
    });
  }
}
