<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Layout from '@/Shared/Layout.vue';
import Breadcrumbs from '@/Shared/Breadcrumbs.vue';
import { ref, defineAsyncComponent, computed } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import { useDialog } from 'primevue/usedialog';
import ConsumablesJournals from './ConsumablesJournals/ConsumablesJournals.vue';
import TabPanel from 'primevue/tabpanel';
import Checkbox from 'primevue/checkbox';
import Panel from 'primevue/panel';
import Card from '@/Shared/Card.vue';
import Title from '@/Shared/Title.vue';
import { Message, Tab, TabList, TabPanels, Tabs } from 'primevue';
import { useAuth } from '@/Composables/useAuth';
import Timestamps from '@/Shared/DataTable/Timestamps.vue';

defineOptions({
  layout: Layout,
});

const {
  consumable,
  consumableCount,
  consumableTitle,
  consumableCountLabels,
  organizations,
  organizationLabels,
  allOrganizations,
} = defineProps({
  consumable: Object,
  consumableCount: Object,
  consumableTitle: String,
  consumableCountLabels: Object,
  organizations: Array,
  organizationLabels: Object,
  allOrganizations: Array,
});

const { can } = useAuth();
const dialog = useDialog();
const title = ref(consumableTitle);

const AddDialog = defineAsyncComponent(() => import('./Dialogs/Add.vue'));

const SubtractDialog = defineAsyncComponent(
  () => import('./Dialogs/Subtract.vue')
);
const CorrectDialog = defineAsyncComponent(
  () => import('./Dialogs/Correct.vue')
);

const openDialog = (dialogComponent, header, data, style = null, breakpoints = null) => {
  dialog.open(dialogComponent, {
    props: {
      header,
      style: style ?? {
        width: '50vw',
      },
      breakpoints: breakpoints ?? {
        '960px': '75vw',
        '640px': '90vw',
      },
      modal: true,
    },
    data,
  });
};

const actions = {
  add: () => {
    openDialog(AddDialog, 'Добавить', {
      id: consumableCount.id,
      consumableCountLabels,
    });
  },
  subtract: () => {
    openDialog(SubtractDialog, 'Вычесть', {
      idConsumable: consumable.id,
      idConsumableCount: consumableCount.id,
    });
  },
  correct: () => {
    openDialog(CorrectDialog, 'Корректировка', {
      consumableCountId: consumableCount.id,
      consumableCountValue: consumableCount.count,
      consumableCountLabels,
    });
  },
};

const bgColor = computed(() =>
  consumableCount.count <= 1
    ? 'bg-red-500'
    : consumableCount.count < 10
      ? 'bg-yellow-500'
      : 'bg-primary-500'
);

const visibleOrganizationsEdit = ref(false);
const form = useForm({
  id_consumable: consumable.id,
  count: 1,
  selectedOrganizations: organizations.map((item) => item.code),
});

const saveOrganizations = () => {
  const url = route('consumables.counts.update-organization', { count: consumableCount.id });
  form.put(url, {
    onSuccess: () => {
      visibleOrganizationsEdit.value = false;
    },
  });
};

const activeTab = ref("0");
</script>
<template>
  <Head :title="title" />

  <Breadcrumbs
    :home="{ label: 'Главная', url: route('dashboard') }"
    :items="[
      {
        label: 'Количество расходных материалов',
        url: route('consumables.counts.index'),
      },
      { label: title },
    ]"
  />

  <Card padding-body-classes="">
    <Title class="ps-6 pt-6">
      {{ title }}
    </Title>
    <Tabs v-model:value="activeTab">
      <TabList>
        <Tab value="0">
          <i class="pi pi-home me-2" />
          Главная
        </Tab>
        <Tab value="1">
          <i class="pi pi-replay me-2" />
          Журнал
        </Tab>
        <Tab value="2">
          <i class="pi pi-list me-2" />
          Организации
        </Tab>
      </TabList>
      <TabPanels>
        <TabPanel value="0">
          <div class="flex items-center justify-between bg-gray-50 p-2 rounded-xl border border-gray-100 shadow-sm w-fit gap-x-8">
            <div class="flex items-center gap-x-4">
              <div class="size-12 text-white rounded-full flex items-center justify-center text-xl font-bold shadow-inner" :class="bgColor">
                {{ consumableCount.count }}
              </div>
              <span class="text-gray-600 font-medium uppercase tracking-wider text-xs">Доступное количество</span>
            </div>
            <div class="w-px bg-gray-500 mx-1" />
            <div class="flex gap-x-1 p-1 bg-white rounded-lg border border-gray-200">
              <Button
                v-if="can('admin', 'add-consumables')"
                v-tooltip="'Добавить'"
                icon="pi pi-plus"
                text
                severity="secondary"
                @click="actions.add"
              />
              <Button
                v-if="can('admin', 'subtract-consumable') && consumableCount.count > 0"
                v-tooltip="'Вычесть'"
                icon="pi pi-minus"
                text
                severity="secondary"
                @click="actions.subtract"
              />
              <template v-if="can('admin')">
                <div class="w-px bg-gray-200 mx-1" />
                <Button
                  v-tooltip="'Корректировка'"
                  icon="pi pi-pencil"
                  text
                  severity="secondary"
                  @click="actions.correct"
                />
              </template>
            </div>
          </div>
          <Timestamps class="mt-6" :created-at="consumableCount.created_at" :updated-at="consumableCount.updated_at" />
        </TabPanel>
        <TabPanel value="1">
          <KeepAlive>
            <ConsumablesJournals
              v-if="activeTab == 1"
              :consumable="consumable"
              :consumable-count="consumableCount"
            />
          </KeepAlive>
        </TabPanel>
        <TabPanel value="2">
          <div v-if="visibleOrganizationsEdit">
            <div>
              <form @submit.prevent="saveOrganizations">
                <Panel header="Редактирование списка организаций">
                  <template #footer>
                    <Button
                      severity="secondary"
                      size="small"
                      label="Назад"
                      @click="visibleOrganizationsEdit = false"
                    />
                    <Button
                      type="submit"
                      :loading="form.processing"
                      class="ms-2"
                      icon="pi pi-save"
                      label="Сохранить"
                      size="small"
                    />
                  </template>
                  <div class="w-1/3">
                    <div id="organizations" class="w-full">
                      <div
                        v-for="organization in allOrganizations"
                        :key="organization.code"
                        class="flex items-center mt-2"
                      >
                        <Checkbox
                          v-model="form.selectedOrganizations"
                          name="organizations"
                          :input-id="organization.code"
                          :value="organization.code"
                        />
                        <label :for="organization.code" class="ml-2 cursor-pointer">
                          {{ `${organization.name} (${organization.code})` }}
                        </label>
                      </div>
                    </div>
                    <Message
                      v-if="form.errors?.selectedOrganizations"
                      class="mt-2"
                      severity="error"
                    >
                      {{ form.errors?.selectedOrganizations }}
                    </Message>
                  </div>
                </Panel>
              </form>
            </div>
          </div>

          <DataTable
            v-else
            :value="organizations"
            data-key="code"
            class="w-full md:w-2/3 lg:w-2/5"
            selection-mode="single"
          >
            <template v-if="can('admin', 'add-consumables')" #header>
              <Button
                severity="success"
                size="small"
                label="Редактировать"
                @click="visibleOrganizationsEdit = true"
              />
            </template>

            <Column :header="organizationLabels.code" field="code" sortable />
            <Column :header="organizationLabels.name" field="name" sortable />

            <template #empty>
              Нет данных
            </template>
          </DataTable>
        </TabPanel>
      </TabPanels>
    </Tabs>
  </Card>
</template>
