export const consumablesUrls = (rootUrl) => {

  const base = `${rootUrl}consumables/counts`;

  return {
    counts: {
      index: () => base,
      create: () => `${base}/create`,
      store: () => base,
      show: (id) => `${base}/${id}`,
      update: (id) => `${base}/${id}`,
      correct: (id) => `${base}/${id}/correct`,
      add: (id) => `${base}/${id}/add`,

      // сохранение вычитаемого расходного материала
      subtract: (consumable, count) => `/consumables/${consumable}/counts/${count}/installed`,
      
      // валидация формы добавления количества расходного материала
      validate: () => `${base}/validate`,
      
      // поиск такого же расходного материала и с той же организацией
      // для дальнейшего прибавления количества, а не создания новой записи
      checkExists: () => `${base}/check-exists`,
      updateOrganizations: (id) => `${base}/${id}/update-organizations`,

      // Последние установленные(вычтенные) расходные материалы
      installed: () => `${base}/installed/last`,
      installMaster: () => `${base}/installed/master`,

      // список расходных материалов, привязанных к принтеру idPrinter     
      listByPrinter: (idPrinter) => `${base}/list-by-printer/${idPrinter}`,

      // журналы движения количества расходных материалов
      journal: {
        // журнал добавления расходных материалов                
        added: {
          index: (idConsumable, idConsumableCount) => `/consumables/${idConsumable}/counts/${idConsumableCount}/added`,
          redo: (idConsumable, idConsumableCount, id) => `/consumables/${idConsumable}/counts/${idConsumableCount}/added/${id}`,
        },
        // журнал установки(вычитания) расходных материалов
        installed: {
          index: (idConsumable, idConsumableCount) => `/consumables/${idConsumable}/counts/${idConsumableCount}/installed`,
          redo: (idConsumable, idConsumableCount, id) => `/consumables/${idConsumable}/counts/${idConsumableCount}/installed/${id}`,
        },
      },
    },
  };
}