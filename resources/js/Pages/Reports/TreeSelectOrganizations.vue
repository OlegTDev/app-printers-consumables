<script setup>
import { TreeSelect } from 'primevue';
import { computed, ref, watch } from 'vue';

const props = defineProps({
  listOrganizations: Object,
  defaultSelectedOrganizations: Object,
});

const emit = defineEmits(['update:selectedOrgs']);

const mapDefaultSelected = (items) => {
  const result = {};

  const recurse = (nodes) => {
    if (!Array.isArray(nodes)) return;

    for (let i = 0;i < nodes.length; i++) {
      const node = nodes[i];
      if (node.key) {
        result[node.key] = true;
      }
      if (node.children) {
        recurse(node.children);
      }
    }
  };

  recurse(items);
  return result;
};

const model = ref(mapDefaultSelected(props.defaultSelectedOrganizations));

const selectedOrgs = computed(() => {
  const activeKeys = [];
  const obj = model.value;

  for (const key in obj) {
    if (Object.hasOwn(obj, key) && obj[key]) {
      activeKeys.push(key);
    }
  }

  return activeKeys;
});

watch(
  () => model.value,
  () => emit('update:selectedOrgs', selectedOrgs.value),
  { deep: true, immediate: true }
);
</script>
<template>
  <TreeSelect
    v-model="model"
    :options="listOrganizations"
    selection-mode="multiple"
    :meta-key-selection="false"
  />
</template>
