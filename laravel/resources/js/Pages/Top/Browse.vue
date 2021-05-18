<template>
    <app-layout>
        <div class="pt-2 pb-12">
            <div class="col-12 mx-auto">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">

                        <data-table
                            :columns="columns"
                            :data-url="this.route('top.getBrowse')"
                            :order-by="'actions_count'"
                            :order-dir="'desc'"
                            :per-page-rows="10"
                        />

                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import DataTable from "@/Components/DataTable";

export default {
    name: "Browse",
    components: {
        AppLayout,
        DataTable,
    },
    data() {
        return {
            columns: [
                {
                    'name': this.__('[ID]'),
                    'showField': 'id',
                    'class': 'w-1/12',
                    'sort': true,
                    'sortField': 'id',
                    'searchField': 'id'
                },
                {
                    'name': this.__('[IP]'),
                    'showField': 'format_ip',
                    'class': '',
                    'sort': true,
                    'sortField': 'ipnum',
                    'searchField': 'ip',
                    'inertiaText': 'format_cidr',
                    'inertiaHrefRoute': 'top.show',
                    'inertiaHrefParams': [{'name': 'id', 'value': 'id'}],
                },
                {
                    'name': this.__('[mask]'),
                    'showField': 'mask',
                    'class': 'w-1/12',
                    'sort': true,
                    'sortField': 'mask',
                    'searchField': 'mask'
                },
                {
                    'name': this.__('[inetnum]'),
                    'showField': 'inetnum',
                    'class': 'w-2/12',
                    'sort': true,
                    'sortField': 'inetnum',
                    'searchField': 'inetnum'
                },
                {
                    'name': this.__('[netname]'),
                    'showField': 'netname',
                    'class': 'w-2/12',
                    'sort': true,
                    'sortField': 'netname',
                    'searchField': 'netname'
                },
                {
                    'name': this.__('[orgname]'),
                    'showField': 'orgname',
                    'class': 'w-2/12',
                    'sort': true,
                    'sortField': 'orgname',
                    'searchField': 'orgname'
                },
                {
                    'name': this.__('[country]'),
                    'showField': 'country',
                    'class': 'w-1/12',
                    'sort': true,
                    'sortField': 'country',
                    'searchField': 'country'
                },
                {
                    'name': this.__('[created_at]'),
                    'showField': 'created_at_dt',
                    'class': 'w-1/12',
                    'sort': true,
                    'sortField': 'created_at',
                    'searchField': 'created_at'
                },
                {
                    'name': this.__('Last check'),
                    'showField': 'last_check_dt',
                    'class': 'w-1/12',
                    'sort': true,
                    'sortField': 'last_check',
                    'searchField': 'last_check'
                },
                {
                    'name': this.__('[Actions]'),
                    'showField': 'actions_count_format',
                    'class': 'w-1/12',
                    'sort': true,
                    'sortField': 'actions_count',
                },
                {
                    'name': this.__('[Total IP]'),
                    'showField': 'total_db_ip_format',
                    'sort': true,
                    'sortField': 'total_ip'
                }
            ]
        }
    },
}
</script>
