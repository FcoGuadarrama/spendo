<script setup lang="ts">
import { Link as InertiaLink } from '@inertiajs/vue3'

defineProps<{
    href: string
    class?: string
}>()
</script>

<template>
    <InertiaLink :href="href" :class="class">
        <slot />
    </InertiaLink>
</template>
