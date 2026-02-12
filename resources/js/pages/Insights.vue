<script setup lang="ts">
import { Head, Form, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { ref } from 'vue';
import axios from 'axios';
import { type BreadcrumbItem } from '@/types';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sync',
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
const cheapest = ref([]);
const expensive = ref([]);
const sendForm = async () => {
    processing.value = true;
    const data = {
        location: location.value,
        start: start.value,
        end: end.value
    }
    await axios.get("/api/insights/prices", {params: data}).then(response => {
        cheapest.value = [];
        expensive.value = [];
        for (const [key, value] of Object.entries(response.data)) {
            if (key == "error") {
                message.value = "Price API Unavailable";
            }
            if (key == "cheapest") {
                cheapest.value.push(value);
            }
            if (key == "expensive") {
                expensive.value.push(value);
            }
        }
        processing.value = false;
    }).catch((e) => {
        console.log(e)
        const erresp = e.response;
        for (const [key, value] of Object.entries(erresp.data)) {
            if (key == 'start') {
                errors.value.start = value;
                message.value = "";
                console.log(errors.value);
            }
            if (key == 'end') {
                errors.value.end = value;
                message.value = "";
            }
            processing.value = false;
        }
    });
}

const deleteData = async () => {
    processing_delete.value = true;
    await axios.delete("/api/readings").then(response => {
        for (const [key, value] of Object.entries(response.data)) {
            if (key == "error") {
                message_delete.value = value;
            }
            if (key == "message") {
                message_delete.value = value;
            }
        }

        processing_delete.value = false;
    }).catch((e) => {
        const erresp = e.response;
        for (const [key, value] of Object.entries(erresp.data)) {
            processing_delete.value = false;
        }
    });
}

</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <h1>Pricing insights</h1>
            <div
                class="relative aspect-video rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
                <div class="grid grid-cols-3 gap-6">
                    <div class="grid gap-2">
                        <Label for="start">Start</Label>
                        <Input id="start" type="datetime-local" autofocus :tabindex="1" autocomplete="name" name="start"
                            placeholder="Start Date" :value="start" @input="start = $event.target.value" />
                        <h2 v-html="errors.start" class="mt-2"></h2>
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">End</Label>
                        <Input id="end" type="datetime-local" autofocus :tabindex="1" autocomplete="name" name="end"
                            placeholder="End Date" :value="end" @input="end = $event.target.value" />
                        <h2 v-html="errors.end" class="mt-2"></h2>
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
                <Button type="submit" class="mt-2 w-full border border-black" tabindex="5" :disabled="processing"
                    data-test="register-user-button" @click="sendForm">
                    <Spinner v-if="processing" />
                    Sync
                </Button>
                <div class="grid grid-cols-2">
                <div v-if="cheapest.length > 0">
                    <h2 class="mt-10">Cheapest Times:</h2>
                    <div v-for="value in cheapest[0]" class="grid">
                        <div class="border border-sidebar-border/70 rounded-xl p-6 m-4 grid">
                                    <h3>Location: {{ value.location }}</h3><h3> Time: {{ new Date(value.created_at).toDateString() }} {{ new Date(value.created_at).getHours() }}:{{ new Date(value.created_at).getMinutes().toString().padStart(2, '0') }}</h3> <h3>Price: €{{ value.price_eur_mwh }}</h3>
                        </div>
                    </div>
                </div>
                <div v-if="expensive.length > 0">
                    <h2 class="mt-10">Most Expensive Times:</h2>
                    <div v-for="value in expensive[0]" class="grid">
                        <div class="border border-sidebar-border/70 rounded-xl p-6 m-4 grid ">
                            <h3>Location: {{ value.location }}</h3><h3> Time: {{ new Date(value.created_at).toDateString() }} {{ new Date(value.created_at).getHours() }}:{{ new Date(value.created_at).getMinutes().toString().padStart(2, '0') }}</h3> <h3>Price: €{{ value.price_eur_mwh }}</h3>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
