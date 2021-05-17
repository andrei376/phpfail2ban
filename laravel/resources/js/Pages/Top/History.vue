<template>
    <div class="position-relative">
        <table class="table table-bordered table-striped table-sm">
            <thead class="table-dark">
            <tr>
                <th colspan="4" class="w-full text-center">{{ this.__('[History]') }}</th>
            </tr>
            <tr>
                <th class="w-2/12">{{ this.__('[Date]') }}</th>
                <th class="w-4/12">{{ this.__('[Agent]') }}</th>
                <th class="w-4/12">{{ this.__('[Jail]') }}</th>
                <th class="w-2/12">{{ this.__('[Action]') }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-if="tableData.length <= 0">
                <td colspan="4" class="text-center">{{ this.__('[No data]') }}</td>
            </tr>
            <tr v-for="row in tableData">
                <td>
                    {{ row.time_format }}
                    <br>
                    ({{ row.time_ago }})
                </td>

                <td>{{ row.agent }}</td>
                <td>{{ row.jail }}</td>
                <td>{{ row.action }}</td>
            </tr>
            </tbody>
        </table>
        <div v-if="isLoading" class="overlay-spinner spinner-border text-primary" role="status" aria-hidden="true"></div>
        <nav v-if="pagination && tableData.length > 0" class="d-flex justify-content-between">
            <span class="d-flex" style="margin-top: 8px;"><i>Displaying {{ pagination.data.length }} of {{ pagination.meta.total }} entries.</i></span>
            <ul class="pagination justify-content-end">
                <li class="page-item" :class="{'disabled' : currentPage === 1}">
                    <a class="page-link" href="#" @click.prevent="$event.target.blur();changePage(currentPage - 1)">Previous</a>
                </li>
                <li v-for="page in pagesNumber" class="page-item"
                    :class="{'active': page === pagination.meta.current_page}">
                    <a href="javascript:void(0)" @click.prevent="$event.target.blur();changePage(page)" class="page-link">{{ page }}</a>
                </li>
                <li class="page-item" :class="{'disabled': currentPage === pagination.meta.last_page }">
                    <a class="page-link" href="#" @click.prevent="$event.target.blur();changePage(currentPage + 1)">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
export default {
    name: "History",
    props: [
        'id'
    ],
    data() {
        return {
            isLoading: false,
            tableData: [],
            pagination: {
                meta: { to: 1, from: 1}
            },
            currentPage: 1,
            offset: 4,
            perPage: this.perPageRows ? this.perPageRows : 10,
            sortedColumn: this.orderBy,
            order: this.orderDir ? this.orderDir : 'desc'
        }
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
    },
    methods: {
        getSyslog: function() {
            this.isLoading = true;

            let postData = {
                page: this.currentPage,
                perPage: this.perPage,
                column: this.sortedColumn,
                order: this.order,
                search: this.field,

                id: this.id,
            };

            axios.get(this.route('top.history', postData)).then(function(response){

                this.pagination = response.data;
                this.tableData = response.data.data;

                this.isLoading = false;
            }.bind(this)).catch(errors => {
                if (errors.response.status == 401) {
                    window.location = this.route('login');
                }
                this.$noty.error(this.__('[Error fetching information.]'), {
                    modal: true
                });
                this.tableData = []

                this.isLoading = false;
            });
        },
        changePage(pageNumber) {
            this.currentPage = pageNumber
            this.getSyslog()
        },
    },
    mounted() {
        this.getSyslog();
    }
}
</script>
