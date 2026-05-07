import { usePage } from '@inertiajs/vue3';

export class Auth {
  can(...roles) {
    const page = usePage();
    const userRoles = page.props.auth?.user?.roles ?? [];

    for (let i = 0; i < roles.length; i++) {
      if (userRoles.indexOf(roles[i]) >= 0) {
        return true;
      }
    }
    return false;
  }
}
