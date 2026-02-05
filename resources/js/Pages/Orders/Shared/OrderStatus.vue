<script setup>
import Tag from 'primevue/tag';
import { computed } from 'vue';

const props = defineProps({
  status: String,
  statuses: Object,
});

const tagStatus = computed(() => {
  if (props.status in props.statuses) {
    return props.statuses[props.status];
  }
})

</script>
<template>
  <Tag :severity="tagStatus.color">
    {{ tagStatus.label }}
  </Tag>
</template>
