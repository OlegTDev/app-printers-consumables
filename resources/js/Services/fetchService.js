import axios from 'axios';

export default {
  /**
   * @param {*} url
   * @returns Array
   */
  async fetch(url) {
    const response = await axios.get(url);
    return response?.data ?? [];
  }

};
