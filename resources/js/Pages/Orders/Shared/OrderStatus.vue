<script setup>
import Tag from 'primevue/tag';

const props = defineProps({
    status: String,
    statusLabel: String,
});

let severity = 'secondary';
if (props.status == 'pending') {
    severity = 'warning';
} else if (props.status == 'rejected' || props.status == 'cancelled') {
    severity = 'danger';
} else if (props.status == 'in_progress') {
    severity = 'info';
} else if (props.status == 'completed') {
    severity = 'success';
}
</script>
<template>
    <Tag :severity="severity">
        {{ statusLabel }}
    </Tag>
</template>