export const printersUrls = (rootUrl) => {
  const base = `${rootUrl}printers/workplace`;

  return {
    index: () => base,
    create: () => `${base}/create`,
    store: () => base,
    show: (id) => `${base}/${id}`,
    edit: (id) => `${base}/${id}/edit`,
    update: (id) => `${base}/${id}`,
    delete: (id) => `${base}/${id}`,

    list: (idConsumable) => `${base}/list/${idConsumable}`,
    all: () => `${base}/all`,
    consumablesInstalled: (id) => `${base}/consumables-installed/${id}`,
  };
};