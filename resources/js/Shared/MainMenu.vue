<script setup>
import { Link } from '@inertiajs/inertia-vue3';
import { usePage } from '@inertiajs/inertia-vue3';
import { inject } from 'vue';

const page = usePage();
const urls = inject('urls');
const auth = inject('auth');

const isUrl = (...urls) => {    
    let currentUrl = page.url.value;
    if (urls[0] === '/') {
        return currentUrl === '/'
    }
    return urls.filter((url) => currentUrl.startsWith(url)).length;
};
const classIsActive = `text-white`;
const classIsInactive = `text-indigo-300 group-hover:text-white`;
const classLink = `flex items-center p-2 rounded-lg hover:bg-white hover:text-indigo-700 group`;

</script>
<template>    
    <div class="h-full px-3 py-4 overflow-y-auto">
        <ul class="space-y-2 font-medium">
            <li>
                <Link :href="urls.home" :class="[isUrl(urls.home) ? classIsActive : classIsInactive, classLink]">
                    <i class="fas fa-home me-3 w-5 h-5"></i>
                    Главная
                </Link>
            </li>

            <li v-if="auth.can('admin')">
                <Link :href="urls.users.index()" :class="[isUrl(urls.users.index()) ? classIsActive : classIsInactive, classLink]">
                    <i class="fas fa-user me-3 w-5 h-5"></i>
                    Пользователи
                </Link>
            </li>

            <li>
                <Link :href="urls.printers.index()" :class="[isUrl(urls.printers.index()) ? classIsActive : classIsInactive, classLink]">
                    <i class="fas fa-print me-3 w-5 h-5"></i>
                    Принтеры
                </Link>
            </li>

            <li>
                <Link :href="urls.consumables.counts.index()" :class="[isUrl(urls.consumables.counts.index()) ? classIsActive : classIsInactive, classLink]">
                    <i class="fas fa-list-ol me-3 w-5 h-5"></i>
                    Количество расходных материалов
                </Link>
            </li>
            
            <li>
                <button type="button" :class="[`w-full text-indigo-300 transition duration-75`, classLink]" aria-controls="dropdown-menu-dictionary" data-collapse-toggle="dropdown-menu-dictionary">                  
                    <i class="fas fa-cube me-3 w-5 h-5"></i>
                    <span class="flex-1 text-left rtl:text-right whitespace-nowrap">Справочники</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                    </svg>
                </button>
                <ul id="dropdown-menu-dictionary" class="hidden py-2 space-y-2">
                    <li>
                        <Link :href="urls.dictionary.printers.index()" :class="[isUrl(urls.dictionary.printers.index()) ? classIsActive : classIsInactive, classLink, 'pl-11']">
                            <i class="fas fa-print me-3 w-5 h-5"></i>
                            Принтеры
                        </Link>
                    </li>
                    <li>
                        <Link :href="urls.dictionary.consumables.index()" :class="[isUrl(urls.dictionary.consumables.index()) ? classIsActive : classIsInactive, classLink, 'pl-11']">
                            <i class="fas fa-box me-3 w-5 h-5"></i>
                            Расходные материалы
                        </Link>
                    </li>
                    <li>
                        <Link :href="urls.dictionary.spareParts.index()" :class="[isUrl(urls.dictionary.spareParts.index()) ? classIsActive : classIsInactive, classLink, 'pl-11']">
                            <i class="fas fa-puzzle-piece me-3 w-5 h-5"></i>
                            Запчасти
                        </Link>
                    </li>
                    <li v-if="auth.can('admin')">
                        <Link :href="urls.dictionary.organizations.index()" :class="[isUrl(urls.dictionary.organizations.index()) ? classIsActive : classIsInactive, classLink, 'pl-11']">
                            <i class="fa-solid fa-sitemap me-3 w-5 h-5"></i>
                            Организации
                        </Link>
                    </li>             
                </ul>
            </li>

            <li>
                <Link :href="urls.reports.index()" :class="[isUrl(urls.reports.index()) ? classIsActive : classIsInactive, classLink]">
                    <i class="far fa-file-excel me-3 w-5 h-5"></i>
                    Отчеты
                </Link>
            </li>

            
        </ul>
    </div>
</template>