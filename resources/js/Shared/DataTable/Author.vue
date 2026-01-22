<script setup>
defineProps({
    login: String,
    fullName: String,
    post: String,
    department: String,
})
</script>
<template>
    <div>
        {{ login }}
    </div>
    <div v-if="fullName">
        {{ fullName }}
    </div>
    <div v-if="post || department">
        {{ post }} - {{ department }}
    </div>
</template>