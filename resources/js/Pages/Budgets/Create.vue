<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import {toast} from "@/Components/ui/toast";

interface Category {
    id: number
    name: string
}

const props = defineProps<{
    categories: Category[]
    year: number
    month: number
}>()

const monthName = computed(() => {
    const date = new Date(props.year, props.month - 1)
    return date.toLocaleDateString('es-MX', { month: 'long', year: 'numeric' })
})

const form = ref({
    category_id: props.categories[0]?.id || null,
    amount: 0,
    year: props.year,
    month: props.month,
})

const errors = ref<Record<string, string>>({})

const submit = () => {
    router.post(route('budgets.store'), form.value, {
        onSuccess: () => {
            toast({ title: 'Presupuesto creado', description: 'El presupuesto ha sido creado exitosamente' })
        },
        onError: (pageErrors) => {
            errors.value = pageErrors as Record<string, string>
            toast({ title: 'Error al crear el presupuesto', description: 'Por favor verifica los datos e intenta nuevamente', variant: 'destructive' })
        },
    })
}
</script>

<template>
    <Head title="Nuevo Presupuesto" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Nuevo Presupuesto
            </h2>
        </template>

        <div class="mx-auto max-w-2xl">
            <Card>
                <CardHeader>
                    <CardTitle>Crear un presupuesto para {{ monthName }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="category_id">Categoría</Label>
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2"
                            >
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <p v-if="errors.category_id" class="mt-1 text-sm text-red-600">{{ errors.category_id }}</p>
                        </div>

                        <div>
                            <Label for="amount">Monto Presupuestado</Label>
                            <Input
                                id="amount"
                                v-model.number="form.amount"
                                type="number"
                                placeholder="0.00"
                                step="0.01"
                                min="0.01"
                                class="mt-1"
                                :class="{ 'border-red-500': errors.amount }"
                            />
                            <p v-if="errors.amount" class="mt-1 text-sm text-red-600">{{ errors.amount }}</p>
                        </div>

                        <div class="flex gap-4">
                            <Button type="submit">Crear Presupuesto</Button>
                            <Button type="button" variant="outline" as-child>
                                <Link :href="route('budgets.index')">Cancelar</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
