<template>
    <table class="table table-striped table-bordered table-sm">
        <thead class="table-dark">
        <tr v-if="tableName">
            <th colspan="7" class="text-center">
                <inertia-link v-if="tableNameLink" :href="tableNameLink">{{ tableName }}</inertia-link>
                <span v-else>{{ tableName }}</span>
            </th>
        </tr>
        <tr>
            <th class="w-2/12">{{ this.__('IP') }}</th>
            <th class="w-3/12">{{ this.__('inetnum') }}</th>
            <th class="w-3/12">{{ this.__('netname') }}</th>
            <th class="w-2/12">{{ this.__('orgname') }}</th>
            <th class="w-1/12">{{ this.__('country') }}</th>
            <th class="w-1/12">{{ this.__('checked') }}</th>
        </tr>
        </thead>
        <tbody>
            <tr v-for="row in tableData">
                <td v-html="row.ip"></td>
                <td>{{ row.inetnum }}</td>
                <td>{{ row.netname }}</td>
                <td>{{ row.orgname }}</td>
                <td>{{ row.country }}</td>
                <td>
                    <span v-if="row.checked" class="badge bg-success mr-5" style="min-width: 4rem;">{{ row.checked }}</span>
                    <span v-else class="badge bg-danger mr-5" style="min-width: 4rem;">{{ row.checked }}</span>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
export default {
    name: "RowTable",
    props: ['tableData', 'tableName', 'tableNameLink']
}
</script>
