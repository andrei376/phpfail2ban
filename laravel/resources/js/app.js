require('./bootstrap');

// Import modules...
import { createApp, h } from 'vue';
import { createInertiaApp, Link } from '@inertiajs/vue3';
import { InertiaProgress } from '@inertiajs/progress';
import mitt from 'mitt';
import crono from 'vue-crono';
import VueNoty from './noty';

window.eventBus = mitt();

const el = document.getElementById('app');

/*createApp({
    render: () =>
        h(createInertiaApp, {
            initialPage: JSON.parse(el.dataset.page),
            resolveComponent: (name) => require(`./Pages/${name}`).default,
        }),
    methods: {
        showAlerts() {
            if (this.$page.props.flash.msg_success !== undefined &&
                this.$page.props.flash.msg_success !== null
            ) {
                this.$noty.success(this.$page.props.flash.msg_success);
            }

            if (this.$page.props.flash.msg_error !== undefined &&
                this.$page.props.flash.msg_error !== null
            ) {
                this.$noty.error(this.$page.props.flash.msg_error, {
                    modal: true
                });
            }

            if (this.$page.props.flash.msg_warning !== undefined &&
                this.$page.props.flash.msg_warning !== null
            ) {
                this.$noty.warning(this.$page.props.flash.msg_warning);
            }

            if (this.$page.props.flash.msg_info !== undefined &&
                this.$page.props.flash.msg_info !== null
            ) {
                this.$noty.info(this.$page.props.flash.msg_info);
            }
        }
    },
    watch: {
        '$page.props.flash': {
            handler() {
                this.showAlerts();
            },
            deep: true,
        },
    }
})
    .mixin({ methods: { route } })
    .mixin(require('./base'))
    .use(VueNoty, {
        theme: 'bootstrap-v4',
        timeout: 1500,
        layout: 'top',
        progressBar: true,
    })
    .use(crono)
    .mount(el);

 */

createInertiaApp({
    resolve: name => require(`./Pages/${name}`),
    setup({ el, App, props, plugin }) {
        createApp({
            render: () => h(App, props),
            methods: {
                showAlerts() {
                    if (this.$page.props.flash.msg_success !== undefined &&
                        this.$page.props.flash.msg_success !== null
                    ) {
                        this.$noty.success(this.$page.props.flash.msg_success);
                    }

                    if (this.$page.props.flash.msg_error !== undefined &&
                        this.$page.props.flash.msg_error !== null
                    ) {
                        this.$noty.error(this.$page.props.flash.msg_error, {
                            modal: true
                        });
                    }

                    if (this.$page.props.flash.msg_warning !== undefined &&
                        this.$page.props.flash.msg_warning !== null
                    ) {
                        this.$noty.warning(this.$page.props.flash.msg_warning);
                    }

                    if (this.$page.props.flash.msg_info !== undefined &&
                        this.$page.props.flash.msg_info !== null
                    ) {
                        this.$noty.info(this.$page.props.flash.msg_info);
                    }
                }
            },
            watch: {
                '$page.props.flash': {
                    handler() {
                        this.showAlerts();
                    },
                    deep: true,
                },
            }
        })
            .use(plugin)
            .component('InertiaLink', Link)
            .mixin({ methods: { route } })
            .mixin(require('./base'))
            .use(VueNoty, {
                theme: 'bootstrap-v4',
                timeout: 1500,
                layout: 'top',
                progressBar: true,
            })
            .use(crono)
            .mount(el)
    },
});

InertiaProgress.init({ color: '#4B5563' });
