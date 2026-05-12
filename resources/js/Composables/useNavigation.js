import { usePage } from "@inertiajs/vue3";

export function useNavigation() {
  const page = usePage();

  const isActiveUrl = (url) => {
    let currentUrl = page.url;
    if (url === '/') {
      return currentUrl === '/';
    }
    return currentUrl.startsWith(url);
  };

  return { isActiveUrl };
}
