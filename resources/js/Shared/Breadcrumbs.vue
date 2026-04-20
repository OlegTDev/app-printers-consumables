<script setup>
import Breadcrumb from 'primevue/breadcrumb'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    home: Object,
    items: Array,
})

</script>

<template>
    <Breadcrumb :home="home" :model="items"  class="mb-2">
        <template #item="{ item, props }">

            <Link v-if="item.url" :href="item.url">
                <span class="text-primary font-semibold">{{ item.label }}</span>
            </Link>

            <a v-else :href="item.url" :target="item.target" v-bind="props.action">
                <span class="text-color">{{ item.label }}</span>
            </a>

        </template>
    </Breadcrumb>
</template>
