export const dictionaryPrintersUrls = (rootUrl) => {
  const base = `${rootUrl}dictionary/printers`;

  return {
    index: () => base,
    create: () => `${base}/create`,
    show: (id) => `${base}/${id}`,
    edit: (id) => `${base}/${id}/edit`,
    update: (id) => `${base}/${id}`,
    delete: (id) => `${base}/${id}`,

    // привязанные расходные материалы к принтеру
    consumables: {
      index: (id) => `${base}/${id}/consumables`,
      add: (idPrinter, idConsumable) => `${base}/${idPrinter}/consumables/${idConsumable}/add`,
      delete: (idPrinter, idConsumable) => `${base}/${idPrinter}/consumables/${idConsumable}`,
    },
  };
};

export const dictionaryConsumablesUrls = (rootUrl) => {
  const base = `${rootUrl}dictionary/consumables`;

  return {
    index: () => base,
    create: () => `${base}/create`,
    store: () => base,
    show: (id) => `${base}/${id}`,
    edit: (id) => `${base}/${id}/edit`,
    update: (id) => `${base}/${id}`,
    delete: (id) => `${base}/${id}`,
    other: (idPrinter) => `${base}/${idPrinter}/other`,

    // привязанные принтеры к расходному материалу
    printers: {
      index: (idConsumable) => `${base}/${idConsumable}/printers`,
      add: (idConsumable, idPrinter) => `${base}/${idConsumable}/printers/${idPrinter}/add`,
      delete: (idConsumable, idPrinter) => `${base}/${idConsumable}/printers/${idPrinter}`,
    },
  };
};

export const dictionaryOrganizationsUrls = (rootUrl) => {
  const base = `${rootUrl}dictionary/organizations`;

  return {
    index: () => base,
    create: () => `${base}/create`,
    store: () => base,
    show: (id) => `${base}/${id}`,
    edit: (id) => `${base}/${id}/edit`,
    update: (id) => `${base}/${id}`,
    delete: (id) => `${base}/${id}`,
  };
};

