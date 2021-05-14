<template>
    <app-layout>
        <div class="pt-2 pb-12">
            <div class="col-12 mx-auto">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <table class="table table-striped table-bordered table-sm">
                            <thead class="table-dark">
                                <tr class="d-flex">
                                    <th scope="col" class="col-3">{{ this.__('[Items]') }}</th>
                                    <th scope="col" class="col-9">{{ this.__('[Rows in db]') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="d-flex">
                                    <td class="col-3">{{ this.__('[IP]') }}</td>
                                    <td class="col-9">{{ ipCount }}</td>
                                </tr>
                                <tr class="d-flex">
                                    <td class="col-3">{{ this.__('[Actions]') }}</td>
                                    <td class="col-9">{{ actionCount }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <br>
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
        'ipCount',
        'actionCount',
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
