<script setup>
import { computed } from 'vue';

const { user } = defineProps({
  user: {
    type: Object,
    required: true,
  },
  classMain: {
    type: String,
    default: 'text-gray-900 text-sm flex items-center gap-1',
  },
  classExtra: {
    type: String,
    default: 'text-sm text-gray-400 pl-6',
  },
});

const tooltip = computed(() => {
  return (user.name ? `УЗ: ${user.name} \n` : '')
    + (user.department ? `${user.department} \n` : '')
    + (user.telephone ? `тел. ${user.telephone}` : '');
});
</script>
<template>
  <div
    v-tooltip="tooltip"
    class="flex flex-col gap-0.5 w-fit"
  >
    <span :class="classMain">
      <div class="grid gap-1.5">
        <div class="font-medium">
          <i class="pi pi-user text-gray-400 me-1" />
          {{ user.fio }}
          <span v-if="!user.fio && user.name">
            ({{ user.name }})
          </span>
        </div>
      </div>
    </span>
    <span v-if="user.post" :class="classExtra">
      {{ user.post }}
    </span>
  </div>
</template>
