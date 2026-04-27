import axios from 'axios';

export default {
  /**
   * @param {*} url
   * @returns Object
   */
  async fetch(url) {
    const response = await axios.get(url);
    if (Array.isArray(response.data.organizations)) {
      return {
        labels: response.data.labels,
        organizations: this.prepareTreeData(response.data.organizations),
      };
    }
    return [];
  },

  /**
   * @param Array data
   * @returns Object|null
   */
  prepareTreeData(data, parent = null) {
    function mapTree(item) {
      return {
        key: item.code,
        label: `${item.code} ${item.name}`,
        code: item.code,
        data: {
          code: item.code,
          name: item.name,
        },
        children: [],
      };
    }

    const lookup = {};
    const items = [];

    for (const item of data) {
      lookup[item.code] = mapTree(item);
    }

    for (const item of data) {
      const currentItem = lookup[item.code];

      if (item.parent === null) {
        items.push(currentItem);
      } else {
        const parentItem = lookup[item.parent];
        if (parentItem) {
          parentItem.children.push(currentItem);
        }
      }
    }
    return items;
  },

  expandAll(nodes) {
    let expanded = {};
    const traverse = (node) => {
      if (node.children && node.children.length > 0) {
        expanded[node.key] = true;
        node.children.forEach(traverse);
      }
    };
    nodes.forEach(traverse);
    return expanded;
  },

}
