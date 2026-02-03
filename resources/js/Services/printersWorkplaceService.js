import axios from 'axios';

export default {
  /**
   * @param {*} url
   * @returns Array
   */
  async fetch(url) {
    const response = await axios.get(url);
    if (Array.isArray(response.data)) {
      return response.data.map((item) => ({
        id: item.id,
        id_printer: item.printer.id,
        location: item.location,
        vendor: item.printer.vendor,
        model: item.printer.model,
        is_color_print: item.printer.is_color_print,
        inventory_number: item.inventory_number,
        serial_number: item.serial_number,
        label: `${item.location} ${item.printer.vendor} ${item.printer.model} ${item.inventory_number} ${item.serial_number}`,
      }));
    }
    return [];
  }
}
