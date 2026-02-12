<script setup lang="ts">
import { Head, Form, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import {ref, provide} from 'vue';
import axios from 'axios';
import { type BreadcrumbItem } from '@/types';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { use } from "echarts/core";
import { CanvasRenderer } from "echarts/renderers";
import { LineChart } from "echarts/charts";
import { TitleComponent, TooltipComponent, LegendComponent, GridComponent } from "echarts/components";
import VChart, { THEME_KEY } from "vue-echarts";


use([CanvasRenderer, LineChart, TitleComponent, TooltipComponent, LegendComponent, GridComponent]);
const data = []
const data2 = []
const data3 = []

const props = defineProps({daily: Object, location: Object, date: Object})
for (const [key, value] of Object.entries(props.daily)) {
    const date = new Date(value.created_at)
    date.setMinutes(date.getMinutes() + 10)
    data.push({"start": value.created_at, "end": date.toISOString(), price: value.price_eur_mwh})
}
for (const [key, value] of Object.entries(props.location)) {
    const date = new Date(value.created_at)
    date.setMinutes(date.getMinutes() + 10)
    data2.push({"start": value.created_at, "end": date.toISOString(), price: value.price_eur_mwh})
}
for (const [key, value] of Object.entries(props.date)) {
    const date = new Date(value.created_at)
    date.setMinutes(date.getMinutes() + 10)
    data3.push({"start": value.created_at, "end": date.toISOString(), price: value.price_eur_mwh})
}
console.log(props.location)

const seriesList = data.map(function(event) {
  let start = event.start;
  let end = event.end;
  let value = event.price;
  if (event.end === null) {
    end = start;  // set end to current time here?
  }
  return {
    type: 'line',
    data: [[start, value], [end, value]],
    symbol: 'none',
    lineStyle: {
      width: 60
    }
  };
})

const seriesList2 = data2.map(function(event) {
  let start = event.start;
  let end = event.end;
  let value = event.price;
  if (event.end === null) {
    end = start;  // set end to current time here?
  }
  return {
    type: 'line',
    data: [[start, value], [end, value]],
    symbol: 'none',
    lineStyle: {
      width: 60
    }
  };
})

const seriesList3 = data3.map(function(event) {
  let start = event.start;
  let end = event.end;
  let value = event.price;
  if (event.end === null) {
    end = start;  // set end to current time here?
  }
  return {
    type: 'line',
    data: [[start, value], [end, value]],
    symbol: 'none',
    lineStyle: {
      width: 60
    }
  };
})

const option = ref({
  xAxis: {
    type: 'time',
  },
  yAxis: {
    type: 'value',
    show: true,
  },
  series: seriesList
});

const option2 = ref({
  xAxis: {
    type: 'time',
  },
  yAxis: {
    type: 'value',
    show: true,
  },
  series: seriesList2
});
const option3 = ref({
  xAxis: {
    type: 'time',
  },
  yAxis: {
    type: 'value',
    show: true,
  },
  series: seriesList3
});


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
const changeLocation = () => {
window.location.href = `/?location=${location.value}`
}
const changeDate = (start, end) => {
    const startiso = new Date(start).toISOString();
    const endiso = new Date(end).toISOString();
window.location.href = `/?start=${startiso}&end=${endiso}`
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
                <h2>Daily Energy Price</h2>
                <VChart class="chart" :option="option" />
            </div>
            <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6"
                >
                <h2>Filtered Energy Price</h2>
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
                <Button
                    type="submit"
                    class="mt-2 w-full border border-black"
                    tabindex="5"
                    :disabled="processing"
                    data-test="register-user-button"
                    @click="changeDate(start, end)"
                >
                    <Spinner v-if="processing" />
                    Sync
                </Button>
                <VChart class="chart" :option="option3" />
            </div>
             <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6"
                >
                <h2>Filtered Energy Price</h2>
                <div class="grid grid-cols-2 gap-2">
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
                    @click="changeLocation"
                >
                    <Spinner v-if="processing" />
                    Sync
                </Button>
                <VChart class="chart" :option="option2" />
            </div>
            
        </div>
    </AppLayout>
</template>
