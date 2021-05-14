<template>
    <input :class="hasError(field) ? 'border-red-700' : 'border-gray-300'" class="focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" v-bind="$attrs" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" ref="input" />
    <div v-for="error in getError(field)" class="text-red-700 text-sm">{{ error }}</div>    
</template>

<script>                
    export default {
        
        props: ['modelValue', 'field'],

        emits: ['update:modelValue'],
        
        methods: {
            focus() {
                this.$refs.input.focus()
            },
            hasError(key) {                
                return this.$page.props.errors.hasOwnProperty(key);
            },
            getError(field) {                                
                return this.$page.props.errors[field];               
            }
        }
    }
</script>

