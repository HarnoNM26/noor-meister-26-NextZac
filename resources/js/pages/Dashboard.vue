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
import { DoughnutChart } from 'vue-chart-3';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const testData = {
      labels: ['Paris', 'Nîmes', 'Toulon', 'Perpignan', 'Autre'],
      datasets: [
        {
          data: [30, 40, 60, 70, 5],
          backgroundColor: ['#77CEFF', '#0079AF', '#123E6B', '#97B0C4', '#A5C8ED'],
        },
      ],
    };

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: true,
  plugins: {
    legend: {
      display: false,
    },
  },
});

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
                <div class="grid grid-cols-2 gap-2">
                <div >
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
                <div>
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
                </div>
                <DoughnutChart :chartData="testData" />
            </div>
        </div>
    </AppLayout>
</template>
