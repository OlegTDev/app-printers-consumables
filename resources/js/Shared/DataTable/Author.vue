<script setup>
defineProps({
  user: {
    type: Object,
    required: true,
  },
  classMain: {
    type: String,
    default: 'font-medium',
  },
  classExtra: {
    type: String,
    default: 'text-xs text-gray-500',
  },
});
</script>
<template>
  <div class="grid gap-y-1">
    <span :class="classMain">
      {{ user.fio }}
      <span v-if="user.name">
        ({{ user.name }})
      </span>
    </span>
    <span v-if="user.post" :class="classExtra">
      {{ user.post }}
    </span>
    <span v-if="user.department" :class="classExtra">
      {{ user.department }}
    </span>
    <span v-if="user.telephone" :class="classExtra">
      {{ user.telephone }}
    </span>
  </div>
</template>
