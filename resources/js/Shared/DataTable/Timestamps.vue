<script setup>
import { useDate } from '@/Composables/useDate';
import { computed } from 'vue';

const { createdAt, updatedAt } = defineProps({
  createdAt: {
    type: String,
    default: null,
  },
  updatedAt: {
    type: String,
    default: null,
  },
  showIcon: {
    type: Boolean,
    default: true,
  },
  iconSize: {
    type: String,
    default: 'text-sm',
  },
  showTooltip: {
    type: Boolean,
    default: true,
  },
});

const { formatDate, fromNow } = useDate();
const tooltip = computed(() => {
  return (createdAt ? `Создано : ${formatDate(createdAt, 'D MMMM YYYY, HH:mm:ss')}\n` : '')
    + (updatedAt ? `Изменено : ${formatDate(updatedAt, 'D MMMM YYYY, HH:mm:ss')}\n` : '');
});
</script>
<template>
  <div
    v-tooltip.bottom="tooltip"
    class="flex flex-col gap-0.5 w-fit text-sm"
  >
    <span class="flex items-center gap-2 font-medium text-gray-900">
      <template v-if="showIcon">
        <i class="pi pi-clock text-gray-400 text-xs shrink-0" :class="iconSize" />
      </template>
      {{ fromNow(updatedAt || createdAt) }}
    </span>
    <span class="text-gray-400 lowercase" :class="showIcon ? 'pl-5' : ''">
      {{ formatDate(updatedAt || createdAt, 'D MMMM YYYY, HH:mm') }}
    </span>
  </div>
</template>
