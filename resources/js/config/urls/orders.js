export const ordersUrls = (rootUrl) => {
  const base = `${rootUrl}orders`;

  return {
    approve: (order) => `${base}/${order}/approve`,
    reject: (order) => `${base}/${order}/reject`,
    completed: (order) => `${base}/${order}/completed`,
    cancel: (order) => `${base}/${order}/cancel`,
    delete: (order) => `${base}/${order}`,
    statusHistory: (order) => `${base}/${order}/status-history`,

    spareParts: ordersSparePartsUrls(rootUrl),
  };
};

const ordersSparePartsUrls = (rootUrl) => {
  const base = `${rootUrl}orders/spare-parts`;

  return {
    index: () => base,
    create: () => `${base}/create`,
    store: () => `${base}`,
    edit: (id) => `${base}/${id}/edit`,
    update: (id) => `${base}/${id}`,
    show: (id) => `${base}/${id}`,
    uploadFile: (idOrderSparePart) => `${base}/${idOrderSparePart}/files`,
    deleteFile: (idOrderSparePart, idOrderSparePartFile) => `${base}/${idOrderSparePart}/files/${idOrderSparePartFile}`,  
  };
};