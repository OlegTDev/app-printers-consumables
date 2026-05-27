import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";

/**
 * @returns {{
 *  user: import('vue').ComputedRef<{
 *    id: number;
 *    name: string;
 *    fio: string;
 *    org_code: string;
 *    email: string;
 *    roles: string[];
 *  } | null>,
 *  isAdmin: import('vue').ComputedRef<boolean>,
 *  userRoles: import('vue').ComputedRef<string[]>,
 *  can: (...roles: string[]) => boolean,
 * }}
 */
export function useAuth() {
  const page = usePage();
  const user = computed(() => page.props.auth?.user);
  const userRoles = computed(() => page.props.auth?.user?.roles ?? []);
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
