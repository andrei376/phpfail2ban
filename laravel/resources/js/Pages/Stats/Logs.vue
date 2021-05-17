<template>
    <app-layout>
        <div class="pt-2 pb-12">
            <div class="col-12">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <table class="table table-striped table-bordered table-sm">
                            <thead class="table-dark">
                            <tr>
                                <th v-for="column in sortColumns" @click="sortByColumn(column.name)" :class="column.class">
                                    {{ column.name }}

                                    <span v-if="column.name === sortedColumn">
                                    <i v-if="order === 'asc' " class="bi bi-caret-up"></i>
                                    <i v-else class="bi bi-caret-down"></i>
                                    </span>
                                </th>
                                <th>{{ this.__('Message') }}</th>
                                <th class="w-2/12">{{ this.__('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-if="tableData.length <= 0">
                                <td :colspan="sortColumns.length + 2" class="text-center">{{ this.__('[No data]') }}</td>
                            </tr>
                            <tr v-for="data in tableData" :key="data.id" class="">
                                <td>{{ data.id }}</td>
                                <td>{{ data.user }}</td>
                                <td>
                                    {{ data.date_format }}
                                    <br>
                                    ({{ data.date_ago }})
                                </td>
                                <td>
                                    <span :class="{ 'text-danger' : data.type === 'delete'}">{{ data.type }}</span>
                                </td>
                                <td v-html="data.message"></td>
                                <td>
                                    <div class="btn-group btn-group-sm w-full" role="group" aria-label="{{ this.__('Actions') }}">
                                        <button v-if="!data.read" @click="readRow(data.id)" type="button" class="btn btn-primary">{{ this.__('Mark read') }}</button>

                                        <button @click="deleteRow(data.id)" type="button" class="btn btn-danger">{{ this.__('Delete') }}</button>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div v-if="isLoading" class="overlay-spinner spinner-border text-primary" role="status" aria-hidden="true"></div>
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
                </div>
            </div>
        </div>
    </app-layout>
</template>


<script>
import AppLayout from "@/Layouts/AppLayout";

export default {
    components: {
        AppLayout
    },
    props: ['sortColumns'],

    data() {
        return {
            tableData: [],
            pagination: {
                meta: { to: 1, from: 1}
            },
            currentPage: 1,
            offset: 4,
            perPage: 5,
            sortedColumn: 'id',
            order: 'desc',
            isLoading: false
        }
    },
    methods: {
        fetchData: function() {
            this.isLoading = true;

            let dataFetchUrl = this.route('log.jslogs', {
                page: this.currentPage,
                perPage: this.perPage,
                column: this.sortedColumn,
                order: this.order
            });

            axios.get(dataFetchUrl).then(function(response){
                this.pagination = response.data;
                this.tableData = response.data.data;
                this.isLoading = false;

                if (this.currentPage > this.pagination.meta.last_page) {
                    this.changePage(this.pagination.meta.last_page);
                }
            }.bind(this)).catch(() => {
                this.tableData = [];
                this.isLoading = false;
            });
        },
        changePage(pageNumber) {
            this.currentPage = pageNumber
            this.fetchData()
        },
        sortByColumn(column) {
            if (column === this.sortedColumn) {
                this.order = (this.order === 'asc') ? 'desc' : 'asc'
            } else {
                this.sortedColumn = column
                this.order = 'asc'
            }
            this.fetchData();
        },
        readRow(id) {
            axios.post(this.route('log.read', id)).then(function() {
                this.$noty.success(this.__("[Log marked read.]"));
                eventBus.emit('refreshStats');
                this.fetchData();
            }.bind(this)).catch(() => {
                this.$noty.error(this.__('[Error marking log read.]'), {
                    modal: true
                });
            });
        },
        deleteRow(id) {
            if (confirm(this.__('[Are you sure you want to delete this log?]'))) {
                axios.delete(this.route('log.delete', id)).then(function () {
                    this.$noty.warning(this.__("[Log deleted.]"));
                    eventBus.emit('refreshStats');
                    this.fetchData();
                }.bind(this)).catch(() => {
                    this.$noty.error(this.__('[Error deleting log.]'), {
                        modal: true
                    });
                });
            }
        }
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
