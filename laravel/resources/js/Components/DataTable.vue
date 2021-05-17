<template>
    <div class="position-relative">
        <table class="table table-sm table-bordered">
            <thead class="table-dark">
                <tr v-if="tableName">
                    <th :colspan="columns.length" class="text-center">{{ tableName }}</th>
                </tr>
                <tr>
                    <th v-for="column in columns" @click="sortByColumn(column.sortField)" :class="column.class">
                        {{ column.name }}

                        <span v-if="column.sortField === sortedColumn">
                                            <i v-if="order === 'asc' " class="bi bi-caret-up float-right"></i>
                                            <i v-else class="bi bi-caret-down float-right"></i>
                        </span>
                        <span v-else-if="column.sort">
                            <i class="bi bi-chevron-bar-expand float-right"></i>
                        </span>
                    </th>
                </tr>
                <tr>
                    <th v-for="column in columns">
                        <div class="form-row" v-if="column.searchField !== undefined">
                            <div class="col-12">
                                <input type="text" class="form-control form-control-sm" v-model="field[column.searchField]">
                            </div>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="(row, index) in tableData"
                    v-bind:style="[(index % 2) === 0 && !isHover[index] ? 'background-color: rgba(0, 0, 0, 0.05);' : '']"
                    @mouseenter="isHover[index]=true;"
                    @mouseleave="isHover[index]=false;"
                    :class="{ 'bg-blue-100' : isHover[index] }"
                >
                    <td v-for="column in columns">
                        <span v-if="column.inertiaText !== undefined">
                            <inertia-link :href="this.route(column.inertiaHrefRoute, this.hrefParams(column.inertiaHrefParams, row))" v-html="row[column.inertiaText]"></inertia-link>
                        </span>

                        <span v-else v-html="row[column.showField]" />
                    </td>
                </tr>
            </tbody>
        </table>
        <div v-if="isLoading" class="overlay-spinner spinner-border text-primary" role="status" aria-hidden="true"></div>
        <nav v-if="pagination && tableData.length > 0" class="d-flex justify-content-between">
            <span class="d-flex" style="margin-top: 8px;"><i>{{ this.__('[Displaying :length of :total entries.]', {'length': pagination.data.length, 'total': pagination.meta.total}) }}</i></span>
            <ul class="pagination justify-content-end">
                <li class="page-item" :class="{'disabled' : currentPage === 1}">
                    <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">{{ this.__('[Previous]') }}</a>
                </li>
                <li v-for="page in pagesNumber" class="page-item"
                    :class="{'active': page === pagination.meta.current_page}">
                    <a href="javascript:void(0)" @click.prevent="changePage(page)" class="page-link">{{ page }}</a>
                </li>
                <li class="page-item" :class="{'disabled': currentPage === pagination.meta.last_page }">
                    <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">{{ this.__('[Next]') }}</a>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
export default {
    name: "DataTable",
    props: {
        'tableName': String,
        'dataUrl': String,
        'orderBy': String,
        'orderDir': String,
        'perPageRows': Number,
        'columns': {
            'name': String,
            'class': String,
            'sort': Boolean,
            'sortField': String,
            'searchField': String,
            'showField': String,
            'inertiaText': String,
            'inertiaHrefRoute': String,
            'inertiaHrefParams': Array,
        }
    },
    created() {
        this.getData();
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

    data() {
        return {
            field: {},
            isLoading: false,
            isHover: [],
            tableData: [],
            pagination: {
                meta: { to: 1, from: 1}
            },
            currentPage: 1,
            offset: 4,
            perPage: this.perPageRows ? this.perPageRows : 5,
            sortedColumn: this.orderBy,
            order: this.orderDir ? this.orderDir : 'asc'
        }
    },

    methods: {
        hrefParams(params, row) {
            let data = new Object();

            for(let i=0; i < params.length; i++) {
                let name = params[i].name;

                data[name] = row[params[i].value];
            }

            return data;
        },
        getData() {
            this.isLoading = true;

            let postData = {
                page: this.currentPage,
                perPage: this.perPage,
                column: this.sortedColumn,
                order: this.order,
                search: this.field
            };

            /*console.log('search=');
            console.log(this.field);
            console.log('sortedColumn=' + this.sortedColumn);

            console.log('url=' + this.dataUrl);
            console.log('GET DATA');*/

            axios.post(this.dataUrl, postData).then(function(response){
                this.pagination = response.data;
                this.tableData = response.data.data;
                this.isLoading = false;
            }.bind(this)).catch(() => {
                this.$noty.error(this.__('Error fetching information.'), {
                    modal: true
                });
                this.tableData = []
                this.isLoading = false;
            });
        },
        changePage(pageNumber) {
            this.currentPage = pageNumber
            this.getData()
        },
        sortByColumn(column) {
            if (column === undefined) {
                return true;
            }

            if (column === this.sortedColumn) {
                this.order = (this.order === 'asc') ? 'desc' : 'asc'
            } else {
                this.sortedColumn = column
                this.order = 'asc'
            }
            this.getData();
        },
    },
    watch: {
        field: {
            handler: _.debounce(function() {
                this.getData();
            }, 350),
            deep: true
        },
        dataUrl: {
            handler: function(){
                this.getData();
            }, deep: true
        }
    }
}
</script>
