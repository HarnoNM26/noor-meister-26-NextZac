<script setup lang="ts">
import { Head, Form, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import {ref} from 'vue';
import axios from 'axios';
import { type BreadcrumbItem } from '@/types';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
const date = new Date()
const location = ref('EE');
const start = ref(null);
const end = ref(null);
const message = ref("");
const processing = ref(false);
const errors = ref({
    start: "",
    end: ""
})
const sendForm = async () => {
    processing.value = true;
     const data = {
        location: location.value,
        start: start.value,
        end: end.value
     }
    await axios.post("/api/sync/prices", data).then(response => {
        for (const [key, value] of Object.entries(response.data)) {
            if(key == "error") {
                 message.value = "Price API Unavailable";
            }
            if(key=="message") {
                message.value = "Successsfully synced with Elering API - " + value
            }
        }
        
        processing.value = false;
    }).catch((e) => {
        const erresp = e.response;
       for (const [key, value] of Object.entries(erresp.data)) {
        if(key == 'start') {
            errors.value.start = value;
            message.value = "";
            console.log(errors.value);
        }
        if(key == 'end') {
            errors.value.end = value;
            message.value = "";
        }
        processing.value = false;
        }   
    });
}

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
        <h1>Elering API Sync</h1>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6"
                >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="start">Start</Label>
                    <Input
                        id="start"
                        type="datetime-local"
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="start"
                        placeholder="Start Date"
                        :value="start"
                        @input="start = $event.target.value"
                    />
                    <h2 v-html="errors.start" class="mt-2" ></h2>
                </div>

                <div class="grid gap-2">
                    <Label for="email">End</Label>
                    <Input
                        id="end"
                        type="datetime-local"
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="end"
                        placeholder="End Date"
                        :value="end"
                        @input="end = $event.target.value"
                    />
                    <h2 v-html="errors.end" class="mt-2" ></h2>
                </div>

                <div class="grid gap-2">
                    <Label for="location">Location</Label>
                    <select name="location" id="location" v-model="location">
                        <option value="EE">EE</option>
                        <option value="LV">LV</option>
                        <option value="FI">FI</option>
                    </select>
                </div>
            </div>
        <Button
                    type="submit"
                    class="mt-2 w-full border border-black"
                    tabindex="5"
                    :disabled="processing"
                    data-test="register-user-button"
                    @click="sendForm"
                >
                    <Spinner v-if="processing" />
                    Sync
                </Button>
                <h2 v-html="message"></h2>
            </div>
        </div>
    </AppLayout>
</template>
