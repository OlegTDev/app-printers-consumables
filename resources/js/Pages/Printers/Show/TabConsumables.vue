<script setup>
import Badge from 'primevue/badge';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';

const props = defineProps({
    consumables: Array,
    consumableLabels: Object,
    cartridgeColors: Object,
    consumableCountLabels: Object,
    consumableTypes: Object,
});

</script>
<template>
    <Card class="mt-4">
        <template #title>Расходные материалы</template>
        <template #content>
            <DataTable :value="consumables" paginator :rows="10" dataKey="id" :metaKeySelection="false" class="w-full"
                tableStyle="min-width: 50rem" selectionMode="single">
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
                                    <div :class="['rounded-full', 'size-4', 'mr-2', cartridgeColors[data.color]['bg']]">
                                    </div>
                                    <div>
                                        {{ cartridgeColors[data.color]['name'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Column>
                <Column :header="consumableCountLabels.count">
                    <template #body="{ data }">
                        <Badge :value="data.consumable_count?.count ?? 0" size="large" :severity="(data.consumable_count?.count ?? 0) <= 1 ? 'danger'
                            : ((data.consumable_count?.count ?? 0) < 10 ? 'warning' : 'success')" />
                    </template>
                </Column>
            </DataTable>
        </template>
    </Card>
</template>