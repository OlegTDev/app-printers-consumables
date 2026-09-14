<script setup>
import { Button, ButtonGroup, Tree } from 'primevue';
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

const handleCheckAll = () => {
  model.value = props.listOrganizations.reduce((acc, item) => ({...acc, [item.code]: true}), {});
};

const handleUncheckAll = () => {
  model.value = {};
};

watch(
  () => model.value,
  () => emit('update:selectedOrgs', selectedOrgs.value),
  { deep: true, immediate: true }
);
</script>
<template>
  <Tree
    v-model:selection-keys="model"
    :value="listOrganizations"
    selection-mode="multiple"
  >
    <template #header>
      <div class="flex items-center gap-2 px-2 mb-2">
        <ButtonGroup>
          <Button
            v-tooltip="`Выбрать все`"
            type="button"
            severity="secondary"
            icon="pi pi-plus"
            @click="handleCheckAll"
          />
          <Button
            v-tooltip="`Отменить выбор всех`"
            type="button"
            severity="secondary"
            icon="pi pi-minus"
            @click="handleUncheckAll"
          />
        </ButtonGroup>
      </div>
    </template>
  </Tree>
</template>
