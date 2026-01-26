export const usersUrls = (rootUrl) => {
  const base = `${rootUrl}users`;
  return {
    index: () => base,
    create: () => `${base}/create`,
    store: () => base,
    edit: (id) => `${base}/${id}/edit}`,
    update: (id) => `${base}/${id}`,
    delete: (id) => `${base}/${id}`,
    restore: (id) => `${base}/${id}/restore`,

    organizations: {
      index: () => `${base}/organizations`,
      change: (id) => `${base}/organizations/${id}`,
    },
  };
}