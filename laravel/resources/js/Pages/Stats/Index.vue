<template>
    <app-layout>
        <div class="pt-2 pb-12">
            <div class="col-12 mx-auto">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        laravelVersion: {{ laravelVersion }}
                        <br>

                        phpVersion: {{ phpVersion }}
                        <br>
                        bootstrap: {{ bootstrapVersion }}
                        <br>
                        vue: {{ vueVersion() }}
                        <br>
                        jQuery: {{ jqueryVersion }}
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout'
import * as Vue from 'vue';
import Button from "@/Jetstream/Button";

export default {
    name: "Index",
    components: {
        Button,
        AppLayout,
    },

    props: [
        'laravelVersion',
        'phpVersion',
    ],
    methods: {
        vueVersion: function(){
            return Vue.version;
        },
    },
    data() {
        return {
            bootstrapVersion: bootstrap.Tooltip.VERSION,
            jqueryVersion: $.fn.jquery,
        }
    }
}
</script>
