<template>
    <div class="mx-auto">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-3 bg-white border-b border-gray-200 divide-y-4 space-y-3 divide-fuchsia-300 position-relative">
                <div class="font-mono font-semibold text-blue-500">{{ this.__('[STATS]') }}</div>

                <div class="pt-1">
                    {{ this.__('[Server load]')}}: {{ statsData.loadAvg[0] }}, {{ statsData.loadAvg[1] }}, {{ statsData.loadAvg[2] }}
                    <br>
                    {{ this.__('[Date]')}}: {{ statsData.date }}
                    <br>
                </div>

                <div class="pt-1">
                    <div class="text-center">{{ this.__('[To check]') }}</div>

                    <div class="w-12/12 inline-block">
                        <span style="min-width: 60px;display:inline-block;">{{ this.__('[IP]') }}:</span>
                        <inertia-link v-if="statsData.checkCount > 0" style="min-width: 35px; display: inline-block; text-align: center;" class="btn btn-sm btn-danger px-1 py-0" :href="this.route('top.check')">{{ statsData.checkCount }}</inertia-link>
                        <span v-else style="min-width: 35px; display: inline-block; text-align: center;" class="rounded px-1 font-bold text-green-700 bg-green-200">{{ statsData.checkCount }}</span>
                    </div>
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
                'checkCount': 0
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
                if (errors.response.status == 401) {
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
        eventBus.all.delete('refreshStats');
        this.$cron.stop('getStats');
    }
}
</script>
