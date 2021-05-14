<template>
    <label :for="for" class="block font-medium text-sm" :class="hasError(field) ? 'text-red-700' : 'text-gray-700'">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>        
    </label>
</template>

<script>
    export default {
        props: ['for', 'value', 'field'],
        
        methods: {
            hasError(key) {
                return this.$page.props.errors.hasOwnProperty(key);
            }
        }
    }
</script>
