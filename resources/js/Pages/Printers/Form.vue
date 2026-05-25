<script setup>
import InputText from 'primevue/inputtext';
import Label from '@/Shared/Label.vue';
import { onMounted, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import TreeSelect from 'primevue/treeselect';
import { useConfig } from '@/Composables/useConfig';
import { useAuth } from '@/Composables/useAuth';
import Select from 'primevue/select';
import Card from '@/Shared/Card.vue';
import FieldRowVertical from '@/Shared/Form/FieldRowVertical.vue';
import Message from 'primevue/message';

const { user } = useAuth();

const props = defineProps({
  labels: {
    type: Object,
    required: true,
  },
  printers: {
    type: Object,
    required: true,
  },
  organizations: {
    type: Array,
    required: true,
  },
  isNew: {
    type: Boolean,
    default: true,
  },
  printerWorkplace: {
    type: Object,
    default: () => ({
      id: null,
      id_printer: null,
      location: null,
      serial_number: null,
      inventory_number: null,
      org_code: null,
    }),
  },
});
const { urls } = useConfig();

const form = useForm({
  id: props.printerWorkplace.id,
  id_printer: props.printerWorkplace.id_printer,
  location: props.printerWorkplace.location,
  serial_number: props.printerWorkplace.serial_number,
  inventory_number: props.printerWorkplace.inventory_number,
  org_code: props.printerWorkplace.org_code ?? user.value?.org_code,
});

const organizationSelected = ref({});

onMounted(() => {
  if (form.org_code) {
    organizationSelected.value = { [form.org_code]: true };
  }
});

const organizationChange = (value) => {
  form.org_code = value ? Object.keys(value).shift() : null;
};

const save = () => {
  if (props.isNew) {
    form.post(urls.printers.store());
  }
  else {
    form.put(urls.printers.update(props.printerWorkplace.id));
  }
};

watch(
  () => ({ ...form.data() }),
  (newValues, oldValues) => {
    Object.keys(newValues).forEach((key) => {
      if (newValues[key] !== oldValues[key] && form.errors[key]) {
        form.errors[key] = null;
      }
    });
  },
  { deep: true }
);
</script>
<template>
  <form @submit.prevent="save">
    <Card class="mb-4">
      <template #default>
        <div class="w-1/2 grid gap-y-10">
          <FieldRowVertical>
            <template #label>
              <Label for="id_printer">{{ labels.id_printer }}</Label>
            </template>
            <template #field>
              <Select
                id="id_printer"
                v-model="form.id_printer"
                filter
                :options="printers"
                option-label="name"
                option-value="id"
                placeholder="Выберите принтер"
              />
            </template>
            <template #message>
              <Message v-if="form.errors?.id_printer" class="mt-2" severity="error">
                {{ form.errors?.id_printer }}
              </Message>
            </template>
          </FieldRowVertical>

          <FieldRowVertical>
            <template #label>
              <Label for="org_code">{{ labels.org_code }}</Label>
            </template>
            <template #field>
              <TreeSelect
                v-model="organizationSelected"
                :options="organizations"
                placeholder="Выберите организацию"
                selection-mode="single"
                :meta-keys-selection="false"
                show-clear
                @update:model-value="organizationChange"
              />
            </template>
            <template #message>
              <Message v-if="form.errors?.org_code" class="mt-2" severity="error">
                {{ form.errors?.org_code }}
              </Message>
            </template>
          </FieldRowVertical>

          <FieldRowVertical>
            <template #label>
              <Label for="location">{{ labels.location }}</Label>
            </template>
            <template #field>
              <InputText
                v-model="form.location"
                :placeholder="labels.location"
                :invalid="form.errors?.location?.length > 0"
              />
            </template>
            <template #message>
              <Message v-if="form.errors?.location" class="mt-2" severity="error">
                {{ form.errors?.location }}
              </Message>
            </template>
          </FieldRowVertical>

          <FieldRowVertical>
            <template #label>
              <Label for="serial_number">{{ labels.serial_number }}</Label>
            </template>
            <template #field>
              <InputText
                v-model="form.serial_number"
                :placeholder="labels.serial_number"
                :invalid="form.errors?.serial_number?.length > 0"
              />
            </template>
            <template #message>
              <Message v-if="form.errors?.serial_number" class="mt-2" severity="error">
                {{ form.errors?.serial_number }}
              </Message>
            </template>
          </FieldRowVertical>

          <FieldRowVertical>
            <template #label>
              <Label for="inventory_number">{{ labels.inventory_number }}</Label>
            </template>
            <template #field>
              <InputText
                v-model="form.inventory_number"
                :placeholder="labels.inventory_number"
                :invalid="form.errors?.inventory_number?.length > 0"
              />
            </template>
            <template #message>
              <Message v-if="form.errors?.inventory_number" class="mt-2" severity="error">
                {{ form.errors?.inventory_number }}
              </Message>
            </template>
          </FieldRowVertical>
        </div>
      </template>
      <template #footer>
        <Button :loading="form.processing" class="font-bold" type="submit" label="Сохранить" />
      </template>
    </Card>
  </form>
</template>
