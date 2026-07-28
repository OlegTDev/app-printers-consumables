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

  const objectToArray = (obj, field) => Array.from(Object.values(obj), (key) => key[field]);

  const page = usePage();
  const user = computed(() => page.props.auth?.user);
  const userRoles = computed(() => objectToArray(page.props.auth?.user?.roles, 'name') ?? []);
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
