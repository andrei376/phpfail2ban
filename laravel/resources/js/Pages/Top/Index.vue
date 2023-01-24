<template>
    <app-layout>
        <div class="pt-2 pb-12">
            <div class="col-12 mx-auto">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">

                        <button v-for="topBy in this.topList" @click="$event.target.blur(); this.changeTop(topBy);" type="button" class="btn mr-4" :class="topBy === showTop ? 'btn-primary' : 'btn-outline-secondary'">
                            {{ topBy }}
                        </button>

                        <div v-if="showTop !== null" class="mt-5">
                            <div v-if="showTop === this.__('[Show by ip1]')">
                                <top-ip1 />
                            </div>
                            <div v-if="showTop === this.__('[Show by country]')">
                                <top-country />
                            </div>
                            <div v-if="showTop === this.__('[Show by actions]')">
                                <top-actions />
                            </div>
                            <div v-if="showTop === this.__('[Show by netname]')">
                                <top-netname />
                            </div>
                            <div v-if="showTop === this.__('[Show by inetnum]')">
                                <top-inetnum />
                            </div>
                            <div v-if="showTop === this.__('[Show by orgname]')">
                                <top-orgname />
                            </div>
                            <div v-if="showTop === this.__('[Show by date added]')">
                                <top-date-added />
                            </div>
                            <div v-if="showTop === this.__('[Show by last check]')">
                                <top-last-check />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import TopIp1 from "@/Pages/Top/TopIp1";
import TopCountry from "@/Pages/Top/TopCountry";
import TopActions from "@/Pages/Top/TopActions";

import TopNetname from "@/Pages/Top/TopNetname";
import TopInetnum from "@/Pages/Top/TopInetnum";
import TopOrgname from "@/Pages/Top/TopOrgname";
import TopDateAdded from "@/Pages/Top/TopDateAdded";
import TopLastCheck from "@/Pages/Top/TopLastCheck";

export default {
    name: "Index",
    components: {
        AppLayout,
        TopIp1,
        TopCountry,
        TopActions,
        TopNetname,
        TopInetnum,
        TopOrgname,
        TopDateAdded,
        TopLastCheck,
    },
    data() {
        return {
            showTop: null,
            topList: [
                this.__('[Show by ip1]'),
                this.__('[Show by country]'),
                this.__('[Show by actions]'),
                this.__('[Show by netname]'),
                this.__('[Show by inetnum]'),
                this.__('[Show by orgname]'),
                this.__('[Show by date added]'),
                this.__('[Show by last check]'),
            ]
        }
    },
    methods: {
        changeTop(value) {
            this.showTop = value;
        }
    }

}
</script>
