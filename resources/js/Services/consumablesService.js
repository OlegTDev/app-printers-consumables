import axios from 'axios';

export default {
  /**
   * @param {*} url
   * @returns Array
   */
  async fetch(url) {
    const response = await axios.get(url);
    if (Array.isArray(response.data?.data)) {
      return response.data.data.map((item) => ({
        id: item.id,
        type: item.type,
        name: item.name,
        color: item.color,
        description: item.description,
        arch: item.arch,
      }));
    }
    return [];
  }
}
