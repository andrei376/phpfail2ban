<template>
    <div class="mx-auto">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-3 bg-white border-b border-gray-200 divide-y-4 space-y-3 divide-fuchsia-300 position-relative">
                <div class="font-mono font-semibold text-blue-500">{{ this.__('STATS') }}</div>

                <div class="pt-1">
                    {{ this.__('Server load')}}: {{ statsData.loadAvg[0] }}, {{ statsData.loadAvg[1] }}, {{ statsData.loadAvg[2] }}
                    <br>
                    {{ this.__('Date')}}: {{ statsData.date }}
                    <br>
                </div>

                <div v-if="isLoading" class="overlay-spinner spinner-border text-primary" role="status" aria-hidden="true"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "FailStats",

    data() {
        return {
            isLoading: false,
            statsData: {
                'loadAvg': ['0', '1', '2'],
                'date': '',
            }
        }
    },
    methods: {
        getStats: function() {
            this.isLoading = true;
            axios.get(this.route('stats')).then(function(response){
                this.statsData = response.data;
                this.isLoading = false;
            }.bind(this)).catch(errors => {
                if (errors.response.status == 401 && errors.response.statusText == 'Unauthorized') {
                    window.location = this.route('login');
                }
                this.isLoading = false;
            });
        }
    },
    mounted: function() {
        this.getStats();
        eventBus.on('refreshStats', () => {
            this.getStats();
        });
    },
    cron: {
        time: 30000,
        method: 'getStats'
    },
    beforeUnmount() {
        this.$cron.stop('getStats');
    }
}
</script>
