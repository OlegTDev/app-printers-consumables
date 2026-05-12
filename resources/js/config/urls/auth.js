export const authUrls = (rootUrl) => ({
  login: () => { return `${rootUrl}login`; },
  logout: () => { return `${rootUrl}logout`; },
});
