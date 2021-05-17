<template>
    <app-layout>
        <div class="pt-2 pb-12">
            <div class="col-12 mx-auto">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">

                        <div class="position-fixed bg-light shadow border-bottom" style="z-index: 4;">
                            <h4 v-if="!ipv6" class="m-0">{{ this.__('[IP]') }}: <span :class="rangeInfo === whoisData.range ? 'text-success' : 'text-danger'">{{ cidrInfo }}{{ hostnameInfo }}</span></h4>
                            <h5 v-else class="m-0">{{ this.__('[IP]') }}: <span :class="cidrInfo === whoisData.range ? 'text-success' : 'text-danger'">{{ cidrInfo }}{{ hostnameInfo }}</span></h5>
                        </div>
                        <br>
                        <br>
                        <div id="1showIpInfo" class="position-relative">
                            <table class="table table-striped table-bordered table-sm">
                                <tbody>
                                <tr class="">
                                    <th scope="row" class="col-1 table-dark text-right" style="width: 10%;">mask</th>

                                    <td class="col-11">{{ ipInfo.mask }} ({{ this.__("total hosts") }} = 2^({{ ipv6 ? 128 : 32 }}-{{ ipInfo.mask }})=
                                        <span class="font-weight-bolder">{{ Intl.NumberFormat('ro-RO').format(Math.pow(2, ((ipv6 ? 128 : 32) - ipInfo.mask))) }}</span>)
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="col-auto table-dark text-right">inetnum</th>
                                    <td>{{ ipInfo.inetnum }}</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="col-auto table-dark text-right">netname</th>
                                    <td>{{ ipInfo.netname }}</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="col-auto table-dark text-right">orgname</th>
                                    <td>{{ ipInfo.orgname }}</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="col-auto table-dark text-right">country</th>
                                    <td>{{ ipInfo.country }}</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="col-auto table-dark text-right">geoipcountry</th>
                                    <td>{{ ipInfo.geoipcountry }}  ({{ geoCountry }})</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="col-auto table-dark text-right">created_at</th>
                                    <td :class="ipInfo.created_at ? 'text-success' : 'text-danger'">
                                        {{ ipInfo.created_at_format }}  ({{ ipInfo.created_at_ago }})
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="col-auto table-dark text-right">last_check</th>
                                    <td :class="ipInfo.last_check ? 'text-success' : 'text-danger'">
                                        {{ ipInfo.last_check_format }}  <span v-if="ipInfo.last_check_ago">({{ ipInfo.last_check_ago }})</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" class="col-auto table-dark text-right">checked</th>
                                    <td>
                                        <span v-if="ipInfo.checked" class="badge bg-success me-5" style="min-width: 4rem;">{{ ipInfo.checked }}</span>
                                        <span v-else class="badge bg-danger me-5" style="min-width: 4rem;">{{ ipInfo.checked }}</span>
                                        <inertia-link class="badge bg-secondary me-2 text-decoration-none hover:text-white" :href="this.route('top.toggle', {'id': ipInfo.id, 'field': 'checked'})">{{ this.__('[Change]') }}</inertia-link>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <div v-if="checkLoading" class="overlay-spinner spinner-border text-primary" role="status" aria-hidden="true"></div>
                        </div>

                        <div id="3showWhoisData" class="position-relative">
                            <table class="table table-striped table-bordered table-sm">
                                <thead class="table-dark">
                                <tr>
                                    <th class="w-2/12">{{ this.__('Whois') }}</th>
                                    <th class="w-2/12">date</th>
                                    <th class="w-3/12">inetnum</th>
                                    <th class="w-2/12">netname</th>
                                    <th class="w-1/12">country</th>
                                    <th class="w-2/12">orgname</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr></tr>
                                <tr class="text-success">
                                    <td>new/current</td>
                                    <td>{{ whoisData.date }}</td>
                                    <td>{{ whoisData.inetnum }}</td>
                                    <td>{{ whoisData.netname }}</td>
                                    <td>{{ whoisData.country }}</td>
                                    <td>{{ whoisData.orgname }}</td>
                                </tr>
                                <tr>
                                    <td :class="checkWhois() ? 'text-success' : 'text-danger'">
                                        <span v-if="checkWhois()">{{ this.__('now in db/same') }}</span>
                                        <span v-else v-html="this.__('now in db/different<br>should update')"></span>
                                    </td>
                                    <td>{{ ipInfo.last_check_format }}</td>
                                    <td :class="whoisData.inetnum !== ipInfo.inetnum ? 'text-danger' : 'text-success'">{{ ipInfo.inetnum ?? 'empty' }}</td>
                                    <td :class="whoisData.netname !== ipInfo.netname ? 'text-danger' : 'text-success'">{{ ipInfo.netname ?? 'empty' }}</td>
                                    <td :class="whoisData.country !== ipInfo.country ? 'text-danger' : 'text-success'">{{ ipInfo.country ?? 'empty' }}</td>
                                    <td :class="whoisData.orgname !== ipInfo.orgname ? 'text-danger' : 'text-success'">{{ ipInfo.orgname ?? 'empty' }}</td>
                                </tr>
                                </tbody>
                            </table>
                            <div v-if="whoisLoading" class="overlay-spinner spinner-border text-primary" role="status" aria-hidden="true"></div>
                            <a @click="showWhois = !showWhois" href="#">{{ this.__('[show full whois output]') }}</a>
                            <div v-show="showWhois" class="whitespace-pre position-relative bg-light" style="z-index: 2;">
                                <div class="text-primary">
                                    {{ whoisData.output }}
                                </div>
                                <div class="text-danger position-fixed bg-light" style="top: 350px; left: 1100px;">
                                    {{ this.__('Information used:') }}<br>
                                    Inetnum: {{ whoisData.inetnum }}<br>
                                    Range: {{ whoisData.range }}<br>
                                    Netname: {{ whoisData.netname }}<br>
                                    Orgname: {{ whoisData.orgname }}<br>
                                    Country: {{ whoisData.country }}
                                </div>
                            </div>
                        </div>

                        <div id="4showDbBtns" class="mt-1">
                            <div>
                                <button type="button" @click="$event.target.blur(); this.forceWhois();" :class="{ 'opacity-25': whoisLoading }" :disabled="whoisLoading" class="btn btn-sm btn-outline-secondary">
                                    <span v-if="whoisLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    {{ this.__('[Force get new whois]') }}
                                </button>

                                <button type="button" @click="$event.target.blur(); this.updateWhois();" :class="{ 'opacity-25': whoisLoading }" :disabled="whoisLoading" class="ml-5 btn btn-sm btn-outline-secondary">
                                    <span v-if="whoisLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    {{ this.__('[Update whois]') }}
                                </button>

                                <button type="button" @click="$event.target.blur(); this.updateLastCheck();" :class="{ 'opacity-25': checkLoading }" :disabled="checkLoading" class="ml-5 btn btn-sm btn-outline-secondary">
                                    <span v-if="checkLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    {{ this.__('[Update last check]') }}
                                </button>
                            </div>
                        </div>

                        <div id="5showDoubledIp">
                            <row-table
                                class="mt-4"
                                v-if="multiple.length > 0"
                                :table-data="multiple"
                                :table-name="this.__('multiple IP') + ' (' + multiple.length + ')'"
                            />
                        </div>

                        <div id="6showOther24Ip">
                            <row-table
                                class="mt-4"
                                v-if="other24.length > 0"
                                :table-data="other24"
                                :table-name="this.__('Other IP in /:searchMask1', {'searchMask1': searchMask1 }) + ' ('+other24.length+')'"
                            />
                        </div>

                        <div id="7showOther16Ip">
                            <row-table
                                class="mt-4"
                                v-if="other16.length > 0"
                                :table-data="other16"
                                :table-name="this.__('Other IP in /:searchMask2', {'searchMask2': searchMask2 }) + ' ('+other16.length+')'"
                            />
                        </div>

                        <div id="9showLogsIp">
                            <log-hits :id="ipInfo.id" :search-ip="ipInfo.format_ip" />
                        </div>

                        <div id="10history">
                            <history :id="ipInfo.id" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import RowTable from "./RowTable";
import LogHits from "./LogHits";
import History from "@/Pages/Top/History";

export default {
    name: "Show",
    components: {
        AppLayout,
        RowTable,
        LogHits,
        History
    },
    props: [
        'ipv6',
        'ipInfo',
        'hostnameInfo',
        'cidrInfo',
        'whoisData',
        'rangeInfo',
        'geoCountry',
        'multiple',
        'other24',
        'other16',
        'searchMask1',
        'searchMask2'
    ],
    data() {
        return {
            showWhois: false,
            whoisLoading: false,
            checkLoading: false,
            ipInfo: this.ipInfo,
            whoisData: this.whoisData
        }
    },
    methods: {
        checkWhois() {
            return (this.$page.props.whoisData.inetnum === this.$page.props.ipInfo.inetnum &&
                this.$page.props.whoisData.netname === this.$page.props.ipInfo.netname &&
                this.$page.props.whoisData.country === this.$page.props.ipInfo.country &&
                this.$page.props.whoisData.orgname === this.$page.props.ipInfo.orgname);
        },
        forceWhois() {
            this.whoisLoading = true;
            let url = this.route('update.show', {
                id: this.ipInfo.id,
                forceWhois: true
            });

            axios.post(url).then(function(response){
                this.whoisData = response.data;

                this.whoisLoading = false;
            }.bind(this)).catch(errors => {
                console.log(errors);
                this.whoisLoading = false
            });
        },
        updateWhois() {
            this.whoisLoading = true;
            this.checkLoading = true;
            let url = this.route('update.show', {
                id: this.ipInfo.id,
                updateWhois: true
            });

            axios.post(url).then(function(response){
                this.$noty.success("Information saved.");
                // this.$page.props.ipInfo = response.data;
                this.ipInfo = response.data;

                eventBus.emit('refreshStats');
                this.whoisLoading = false;
                this.checkLoading = false;
            }.bind(this)).catch(() => this.whoisLoading = false);
        },
        updateLastCheck() {
            this.checkLoading = true;
            let url = this.route('update.show', {
                id: this.ipInfo.id,
                list: this.list,
                updateLastCheck: true
            });

            axios.post(url).then(function (response) {
                this.$noty.success("Information saved.");
                // this.$page.props.ipInfo = response.data;
                this.ipInfo = response.data;

                eventBus.emit('refreshStats');
                this.checkLoading = false;
            }.bind(this)).catch(() => this.checkLoading =false);
        },
    }
}
</script>
