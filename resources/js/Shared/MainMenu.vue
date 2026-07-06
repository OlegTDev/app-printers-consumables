<script setup>
import { Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { useNavigation } from '@/Composables/useNavigation';
import { useAuth } from '@/Composables/useAuth';
import HomeIcon from '~icons/fa7-regular/home.vue';
import UsersIcon from '~icons/tabler/users.vue';
import PrinterIcon from '~icons/system-uicons/printer.vue';
import CounterIcon from '~icons/fluent/counter-24-regular.vue';
import DictionaryIcon from '~icons/material-symbols-light/dictionary-outline-sharp.vue';
import DropletsIcon from '~icons/lucide/droplets.vue';
import OrganizationIcon from '~icons/fluent/organization-16-regular.vue';
import OrderIcon from '~icons/mdi/cart-outline.vue';
import PrinterWrenchIcon from '~icons/mdi/printer-pos-wrench-outline.vue';
import InkColorIcon from '~icons/mdi/ink-color.vue';
import MiscIcon from '~icons/mdi/toy-brick-outline.vue';
import ReportIcon from '~icons/ix/report-text.vue';

const { isActive } = useNavigation();
const { isAdmin } = useAuth();

const classIsActive = `text-white bg-indigo-800`;
const classIsInactive = `text-indigo-300 hover:bg-white hover:text-indigo-700 group-hover:text-white`;
const classLink = `flex items-center p-2 rounded-lg group transition-colors`;

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
    icon: HomeIcon,
    href: route('home'),
    show: true,
    active: isActive('dashboard') || isActive('home'),
    dropdown: false,
  },
  {
    id: '02',
    name: 'Пользователи',
    icon: UsersIcon,
    href: route('users.index'),
    show: isAdmin.value,
    active: isActive('users.index'),
    dropdown: false,
  },
  {
    id: '03',
    name: 'Принтеры на рабочих местах',
    icon: PrinterIcon,
    href: route('workplace.index'),
    show: true,
    active: isActive('workplace.index'),
    dropdown: false,
  },
  {
    id: '04',
    name: 'Количество расходных материалов',
    icon: CounterIcon,
    href: route('consumables.counts.index'),
    show: true,
    active: isActive('consumables.counts.index'),
    dropdown: false,
  },
  {
    id: '05',
    name: 'Справочники',
    icon: DictionaryIcon,
    show: true,
    active: isActive('dictionary'),
    dropdown: true,
    children: [
      {
        id: '05-01',
        name: 'Принтеры',
        icon: PrinterIcon,
        href: route('dictionary.printers.index'),
        show: true,
        active: isActive('dictionary.printers'),
      },
      {
        id: '05-02',
        name: 'Расходные материалы',
        icon: DropletsIcon,
        href: route('dictionary.consumables.index'),
        show: true,
        active: isActive('dictionary.consumables'),
      },
      {
        id: '05-03',
        name: 'Организации',
        icon: OrganizationIcon,
        href: route('dictionary.organizations.index'),
        show: isAdmin.value,
        active: isActive('dictionary.organizations'),
      },
    ],
  },
  {
    id: '06',
    name: 'Заказы',
    icon: OrderIcon,
    show: true,
    active: isActive('orders'),
    dropdown: true,
    children: [
      {
        id: '06-01',
        name: 'Запчасти для принтера',
        icon: PrinterWrenchIcon,
        href: route('orders.spare-parts.index'),
        show: true,
        active: isActive('orders.spare-parts'),
      },
      {
        id: '06-02',
        name: 'Картриджи',
        icon: InkColorIcon,
        href: route('orders.consumables.index'),
        show: true,
        active: isActive('orders.consumables'),
      },
      {
        id: '06-03',
        name: 'Мелочи',
        icon: MiscIcon,
        href: route('orders.misc.index'),
        show: true,
        active: isActive('orders.misc'),
      },
    ],
  },
  {
    id: '07',
    name: 'Отчеты',
    icon: ReportIcon,
    href: route('reports.index'),
    active: isActive('reports'),
    show: true,
    dropdown: false,
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
              <component
                :is="item.icon"
                class="me-3 w-5 h-5 shrink-0"
              />
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
              <component
                :is="item.icon"
                class="me-3 w-5 h-5 shrink-0"
              />
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
                      <component
                        :is="subItem.icon"
                        class="me-3 w-5 h-5 shrink-0"
                      />
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
<style scoped>
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
