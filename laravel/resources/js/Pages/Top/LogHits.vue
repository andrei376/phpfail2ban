<template>
    <div v-if="tableData.length > 0">
        <div>
            <table class="table table-striped table-bordered table-sm">
                <thead class="table-dark">
                <tr>
                    <th class="w-2/12">{{ this.__('Date') }}</th>
                    <th class="w-10/12">{{ this.__('Message') }}</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="data in tableData" :key="data.id" class="">
                    <td>
                        {{ data.date_format }}
                        <br>
                        ({{ data.date_ago }})
                    </td>
                    <td v-html="data.message"></td>
                </tr>
                </tbody>
            </table>
        </div>
        <nav v-if="pagination && tableData.length > 0" class="d-flex justify-content-between">
            <span class="d-flex" style="margin-top: 8px;"><i>Displaying {{ pagination.data.length }} of {{ pagination.meta.total }} entries.</i></span>
            <ul class="pagination justify-content-end">
                <li class="page-item" :class="{'disabled' : currentPage === 1}">
                    <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Previous</a>
                </li>
                <li v-for="page in pagesNumber" class="page-item"
                    :class="{'active': page === pagination.meta.current_page}">
                    <a href="javascript:void(0)" @click.prevent="changePage(page)" class="page-link">{{ page }}</a>
                </li>
                <li class="page-item" :class="{'disabled': currentPage === pagination.meta.last_page }">
                    <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
export default {
    name: "LogHits",
    props: [
        'id',
        'searchIp'
    ],
    data() {
        return {
            tableData: [],
            pagination: {
                meta: { to: 1, from: 1}
            },
            currentPage: 1,
            offset: 4,
            perPage: 5,
        }
    },

    methods: {
        fetchData: function() {
            let dataFetchUrl = this.route('ip.log', {
                page: this.currentPage,
                perPage: this.perPage,
                id: this.$props.id,
                searchIp: this.$props.searchIp
            });

            axios.get(dataFetchUrl).then(function(response){
                this.pagination = response.data;
                this.tableData = response.data.data;
            }.bind(this)).catch(() => this.tableData = []);
        },
        changePage(pageNumber) {
            this.currentPage = pageNumber
            this.fetchData()
        },
    },
    created() {
        this.fetchData();
    },
    computed: {
        pagesNumber() {
            if (!this.pagination.meta.to) {
                return [];
            }

            let from = this.pagination.meta.current_page - this.offset;
            if (from < 1) {
                from = 1;
            }

            let to = from + (this.offset * 2);
            if (to >= this.pagination.meta.last_page) {
                to = this.pagination.meta.last_page;
            }
            let pagesArray = [];
            for (let page = from; page <= to; page++) {
                pagesArray.push(page);
            }
            return pagesArray;
        },
        totalData() {
            return (this.pagination.meta.to - this.pagination.meta.from) + 1;
        }
    }
}
</script>
