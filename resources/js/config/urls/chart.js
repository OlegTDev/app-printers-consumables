export const chartUrls = (rootUrl) => {
  const base = `${rootUrl}chart`;
  
  return {
    last: () => `${base}/last`,
    lastAdded: () => `${base}/last-added`,
    lastInstalled: () => `${base}/last-installed`,
  };
}