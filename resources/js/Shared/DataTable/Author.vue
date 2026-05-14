<script setup>
defineProps({
  user: {
    type: Object,
    required: true,
  }
})
</script>
<template>
  <div class="grid gap-y-1">
    <span class="font-medium">
      {{ user.fio }}
      <span v-if="user.name">
        ({{ user.name }})
      </span>
    </span>
    <span v-if="user.post" class="text-xs text-gray-500">
      {{ user.post }}
    </span>
    <span v-if="user.department" class="text-xs text-gray-500">
      {{ user.department }}
    </span>
    <span v-if="user.telephone" class="text-xs text-gray-500">
      {{ user.telephone }}
    </span>
  </div>
</template>
