<script setup>
import { useDate } from '@/Composables/useDate';

defineOptions({
  inheritAttrs: false,
});

defineProps({
  items: {
    type: Array,
    required: true,
    default: () => [],
  },
  classRow: {
    type: [String, Object, Array],
    default: '',
  },
});

const { fromNow, formatDate } = useDate();
</script>
<template>
  <table
    class="w-1/2 text-left text-gray-700"
    :class="classRow"
  >
    <tr
      v-for="item in items"
      :key="item.value"
      class="bg-white border-b border-b-gray-200"
      :class="$attrs.classRow"
    >
      <th
        scope="row"
        class="px-6 py-4"
        :class="item?.classTh"
      >
        {{ item.label }}
      </th>

      <td class="px-6 py-4" :class="item?.classTd">
        <template v-if="item.value && (item?.is_date ?? false)">
          <i v-if="item?.icon" :class="item.icon" />
          {{ fromNow(item.value) }}
          ({{ formatDate(item.value) }})
        </template>
        <template v-else>
          {{ item.value }}
        </template>
      </td>
    </tr>
  </table>
</template>
