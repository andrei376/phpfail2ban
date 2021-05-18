<template>
    <div>
        <data-table
            :columns="columns"
            :data-url="this.route('top.actions')"
            :order-by="'actions_count'"
            :order-dir="'desc'"
            :per-page-rows="10"
        />
    </div>
</template>

<script>

import DataTable from "@/Components/DataTable";

export default {
    name: "TopActions",
    components: {
        DataTable
    },
    data() {
        return {
            columns: [
                {
                    'name': this.__('[index]'),
                    'showField': 'index',
                    'sort': false,
                    'class': 'w-1/12'
                },
                {
                    'name': this.__('[IP]'),
                    'showField': 'format_cidr',
                    'class': 'w-1/12',
                    'sort': true,
                    'sortField': 'format_cidr',
                    'searchField': 'format_cidr',
                    'inertiaText': 'format_cidr',
                    'inertiaHrefRoute': 'top.show',
                    'inertiaHrefParams': [{'name': 'id', 'value': 'id'}],
                },
                {
                    'name': this.__('[netname]'),
                    'showField': 'netname',
                    'class': 'w-7/12',
                    'sort': true,
                    'sortField': 'netname',
                    'searchField': 'netname'
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
                    'name': this.__('[Total actions]'),
                    'showField': 'actions_count_format',
                    'class': 'w-1/12',
                    'sort': true,
                    'sortField': 'actions_count',
                },
                {
                    'name': this.__('[Total IP]'),
                    'showField': 'total_db_ip_format',
                    'class': 'w-1/12',
                    'sort': true,
                    'sortField': 'total_ip'
                }
            ]
        }
    }
}
</script>
