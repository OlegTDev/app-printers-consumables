<script setup>
import { useDate } from '@/Composables/useDate';

defineProps({
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
    default: '22px',
  },
  showTooltip: {
    type: Boolean,
    default: true,
  },
});

const { formatDate, fromNow } = useDate();
</script>
<template>
  <div class="flex items-center gap-2">
    <div v-if="showIcon" class="w-10 flex items-center justify-center">
      <i class="pi pi-calendar" :style="{ fontSize: iconSize }" />
    </div>
    <div class="grid gap-2 flex-1 min-w-0">
      <span
        v-if="createdAt"
        v-tooltip="showTooltip ? `${formatDate(createdAt)}` : null"
        class="w-fit"
      >
        Создано: {{ fromNow(createdAt) }}
      </span>
      <span
        v-if="updatedAt && createdAt != updatedAt"
        v-tooltip="showTooltip ? `${formatDate(updatedAt)}` : null"
        class="w-fit"
      >
        Изменено: {{ fromNow(updatedAt) }}
      </span>
    </div>
  </div>
</template>
