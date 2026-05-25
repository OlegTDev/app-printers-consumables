<script setup>
import { Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { useNavigation } from '@/Composables/useNavigation';
import { useAuth } from '@/Composables/useAuth';
import { useConfig } from '@/Composables/useConfig';

const { urls } = useConfig();

const { isActiveUrl, isActive } = useNavigation();
const { isAdmin } = useAuth();

const classIsActive = `text-white`;
const classIsInactive = `text-indigo-300 group-hover:text-white`;
const classLink = `flex items-center p-2 rounded-lg hover:bg-white hover:text-indigo-700 group`;

const openMenus = ref([]);

const toggleMenu = (id) => {
  const index = openMenus.value.indexOf(id);
  if (index > -1) {
    openMenus.value.splice(index, 1);
  } else {
    openMenus.value.push(id);
  }
};

const isOpen = (id) => {
  return openMenus.value.includes(id);
};

const menu = computed(() => [
  {
    id: '01',
    name: 'Главная',
    icon: 'fas fa-home me-3 w-5 h-5',
    href: urls.home,
    show: true,
    // active: isActiveUrl(urls.home),
    active: isActive('dashboard'),
    dropdown: false,
  },
  {
    id: '02',
    name: 'Пользователи',
    icon: 'fas fa-user me-3 w-5 h-5',
    href: urls.users.index(),
    show: isAdmin.value,
    active: isActiveUrl(urls.users.index()),
    dropdown: false,
  },
  {
    id: '03',
    name: 'Принтеры',
    icon: 'fas fa-print me-3 w-5 h-5',
    href: urls.printers.index(),
    show: true,
    active: isActiveUrl(urls.printers.index()),
    dropdown: false,
  },
  {
    id: '04',
    name: 'Количество расходных материалов',
    icon: 'fas fa-list-ol me-3 w-5 h-5',
    href: urls.consumables.counts.index(),
    show: true,
    active: isActiveUrl(urls.consumables.counts.index()),
    dropdown: false,
  },
  {
    id: '05',
    name: 'Справочники',
    icon: 'fas fa-cube me-3 w-5 h-5',
    show: true,
    active: isActive('dictionary'),
    dropdown: true,
    children: [
      {
        id: '05-01',
        name: 'Принтеры',
        icon: 'fas fa-print me-3 w-5 h-5',
        // href: urls.dictionary.printers.index(),
        href: route('dictionary.printers.index'),
        show: true,
        active: isActiveUrl(urls.dictionary.printers.index()),
      },
      {
        id: '05-02',
        name: 'Расходные материалы',
        icon: 'fas fa-box me-3 w-5 h-5',
        href: urls.dictionary.consumables.index(),
        show: true,
        active: isActiveUrl(urls.dictionary.consumables.index()),
      },
      {
        id: '05-03',
        name: 'Организации',
        icon: 'fas fa-sitemap me-3 w-5 h-5',
        href: urls.dictionary.organizations.index(),
        show: isAdmin.value,
        active: isActiveUrl(urls.dictionary.organizations.index()),
      },
    ],
  },
  {
    id: '06',
    name: 'Заказы',
    icon: 'fas fa-dolly me-3 w-5 h-5',
    show: true,
    active: isActiveUrl('/order'),
    dropdown: true,
    children: [
      {
        id: '06-01',
        name: 'Запчасти для принтера',
        icon: 'fas fa-gears me-3 w-5 h-5',
        href: urls.orders.spareParts.index(),
        show: true,
        active: isActiveUrl(urls.orders.spareParts.index()),
      },
      {
        id: '06-02',
        name: 'Картриджи',
        icon: 'fas fa-box me-3 w-5 h-5',
        href: urls.orders.consumables.index(),
        show: true,
        active: isActiveUrl(urls.orders.consumables.index()),
      },
      {
        id: '06-03',
        name: 'Мелочи',
        icon: 'fas fa-puzzle-piece me-3 w-5 h-5',
        href: urls.orders.misc.index(),
        show: true,
        active: isActiveUrl(urls.orders.misc.index()),
      },
    ],
  },
]);

onMounted(() => {
  menu.value.forEach((item) => {
    if (item.dropdown && item.active) {
      openMenus.value.push(item.id);
    }
  });
});
</script>
<template>
  <div class="h-full px-3 py-4 overflow-y-auto">
    <ul class="space-y-2 font-medium">
      <template v-for="item in menu" :key="item.id">
        <li v-if="item.show">
          <template v-if="!item.dropdown">
            <Link :href="item.href" :class="[item.active ? classIsActive : classIsInactive, classLink]">
              <i :class="item.icon" />
              {{ item.name }}
            </Link>
          </template>
          <template v-else>
            <button
              v-if="item.show"
              type="button"
              :class="[item.active ? classIsActive : classIsInactive, `w-full text-indigo-300 transition duration-75 cursor-pointer`, classLink]"
              @click="toggleMenu(item.id)"
            >
              <i :class="item.icon" />
              {{ item.name }}
              <svg
                :class="['w-3 h-3 ms-3 transition-transform duration-200', isOpen(item.id) ? 'rotate-180' : '']"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 10 6"
              >
                <path
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="m1 1 4 4 4-4"
                />
              </svg>
            </button>
            <Transition name="dropdown">
              <ul v-show="isOpen(item.id)" class="py-2 space-y-2 overflow-hidden">
                <template v-for="subItem in item.children" :key="subItem.href">
                  <li v-if="subItem.show">
                    <Link :href="subItem.href" :class="[subItem.active ? classIsActive : classIsInactive, classLink, 'pl-11']">
                      <i :class="subItem.icon" />
                      {{ subItem.name }}
                    </Link>
                  </li>
                </template>
              </ul>
            </Transition>
          </template>
        </li>
      </template>
    </ul>
  </div>
</template>
<style>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.1s ease-out;
  max-height: 500px;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  max-height: 0;
  transform: translateY(-10px);
}
</style>
