<template>
    <div>
        <data-table
            :columns="columns"
            :data-url="this.route('top.ip1')"
            :order-by="'total_ip'"
            :order-dir="'desc'"
            :per-page-rows="10"
        />
    </div>
</template>

<script>

import DataTable from "@/Components/DataTable";

export default {
    name: "TopIp1",
    components: {
        DataTable
    },
    data() {
        return {
            columns: [
                {
                    'name': this.__('index'),
                    'showField': 'index',
                    'sort': false,
                    'class': 'w-1/12'
                },
                {
                    'name': this.__('ip1'),
                    'showField': 'ip1',
                    'class': 'w-6/12',
                    'sort': true,
                    'sortField': 'ip1',
                    'searchField': 'ip1'
                },
                {
                    'name': this.__('[DB Rows]'),
                    'showField': 'row_count_format',
                    'class': 'w-2/12',
                    'sort': true,
                    'sortField': 'row_count',
                },
                {
                    'name': this.__('Total IP'),
                    'showField': 'total_db_ip_format',
                    'sort': true,
                    'sortField': 'total_ip'
                }
            ]
        }
    }
}
</script>
