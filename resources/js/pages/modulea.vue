<script setup>
import {ref, onMounted} from "vue"
import axios from 'axios'
const loading = ref(true);
const message = ref("");

onMounted(async () => {
    await axios("api/health").then(response => {
        loading.value = false;
        message.value = "Backend OK";
    }).catch(error => {
        message.value = "Esineb hetke seisuga tõrkeid teenusega.";
    })
})
</script>
<template>
    <div>
        {{message}}
    </div>
    <template v-if="loading">
        Rakendus teeb taustas päringut...
    </template>
</template>