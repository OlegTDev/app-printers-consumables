<script setup>
import Badge from 'primevue/badge';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';

defineProps({
  consumables: Array,
  consumableLabels: Object,
  cartridgeColors: Object,
  consumableCountLabels: Object,
  consumableTypes: Object,
});
</script>
<template>
  <DataTable
    :value="consumables"
    paginator
    :rows="10"
    data-key="id"
    :meta-key-selection="false"
    class="w-full"
    table-style="min-width: 50rem"
    selection-mode="single"
  >
    <Column :header="consumableLabels.type" field="type">
      <template #body="{ data }">
        {{ consumableTypes[data.type] }}
      </template>
    </Column>
    <Column :header="consumableLabels.name" field="consumable.name">
      <template #body="{ data }">
        <div class="grid grid-rows-2 gap-4">
          <div>
            {{ data.name }}
          </div>
          <div v-if="data.type === 'cartridge'">
            <div class="flex">
              <div
                :class="[
                  'rounded-full',
                  'size-4',
                  'mr-2',
                  cartridgeColors[data.color]?.bg,
                ]"
              />
              <div>
                {{ cartridgeColors[data.color]?.name }}
              </div>
            </div>
          </div>
        </div>
      </template>
    </Column>
    <Column :header="consumableCountLabels.count">
      <template #body="{ data }">
        <Badge
          :value="data.consumable_count?.count ?? 0"
          size="large"
          :severity="
            (data.consumable_count?.count ?? 0) <= 1
              ? 'danger'
              : (data.consumable_count?.count ?? 0) < 10
                ? 'warning'
                : 'success'
          "
        />
      </template>
    </Column>
  </DataTable>
</template>
