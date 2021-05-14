<template>
    <app-layout>
        <div class="pt-2 pb-12">
            <div class="sm:max-w-md mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="update" novalidate>

                            <user-fields :form="form" />

                            <div class="flex items-center justify-between mt-4">
                                <inertia-link class="btn btn-primary" :href="this.route('users.index')">{{ this.__('[Back]') }}</inertia-link>

                                <button type="submit" @click="$event.target.blur();" class="btn btn-success" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    {{ this.__('[Save]') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>


<script>
    import AppLayout from "@/Layouts/AppLayout";
    import UserFields from "@/Pages/User/UserFields";

    export default {

        components: {
            AppLayout,
            UserFields
        },

        props: {
            'user': Object
        },

        remember: 'form',

        data() {
            return {
                form: this.$inertia.form({
                    name: this.user.name,
                    email: this.user.email,
                    password: '',
                    password_confirmation: '',
                    terms: false,
                })
            }
        },

        methods: {
            update() {
                this.form.put(this.route('users.update', this.user.id));
            }
        }
    }
</script>
