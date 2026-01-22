<script setup>
import { inject } from 'vue';

defineProps({
    created_at: String,
    updated_at: String,
})

const moment = inject('moment');
</script>
<template>
    <div class="grid grid-rows-2 gap-2">
        <div v-if="created_at" v-tooltip="`Создано: ${moment(created_at).format('LLLL')}`">
            <i class="far fa-calendar"></i>
            {{ moment(created_at).fromNow() }}
        </div>                    
        <div v-if="updated_at && created_at != updated_at" v-tooltip="`Изменено: ${moment(updated_at).format('LLLL')}`">
            <i class="far fa-calendar-alt"></i>      
            {{ moment(updated_at).fromNow() }}
        </div>
    </div>
</template>