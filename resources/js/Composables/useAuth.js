import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";

export function useAuth() {
  const page = usePage();
  const user = computed(() => page.props.auth?.user);
  const userRoles = computed(() =>page.props.auth?.user?.roles ?? []);
  const isAdmin = computed(() => userRoles.value.includes('admin'));

  const can = (...roles) => {
    return roles.some(role => userRoles.value.includes(role));
  };

  return {
    user,
    isAdmin,
    userRoles,
    can,
  };
}
