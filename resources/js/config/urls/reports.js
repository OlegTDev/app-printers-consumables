export const reportsUrls = (rootUrl) => {
  const base = `${rootUrl}reports`;

  return {
    index: () => base,
    exportPrintersWorkplace: () => `${base}/export-printers-workplace`,
    exportConsumableCount: () => `${base}/export-consumable-count`,
    exportConsumableInstalledCount: () => `${base}/export-consumable-installed-count`,
  };
};