<script setup lang="ts">
import { ref } from 'vue'
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

interface Budget {
    id: number
    category_id: number
    amount: number
    year: number
    month: number
}

const props = defineProps<{
    budget: Budget
    categories: Category[]
}>()

const form = ref({
    category_id: props.budget.category_id,
    amount: props.budget.amount,
    year: props.budget.year,
    month: props.budget.month,
})

const errors = ref<Record<string, string>>({})

const submit = () => {
    router.put(route('budgets.update', props.budget.id), form.value, {
        onSuccess: () => {
            toast({ title: 'Éxito', description: 'Presupuesto actualizado exitosamente' })
            router.visit(route('budgets.index'))
        },
        onError: (pageErrors) => {
            toast({ title: 'Error', description: 'No se pudo editar el presupuesto', variant: 'destructive' })
            errors.value = pageErrors as Record<string, string>
        },
    })
}
</script>

<template>
    <Head :title="`Editar Presupuesto`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Editar Presupuesto
            </h2>
        </template>

        <div class="mx-auto max-w-2xl">
            <Card>
                <CardHeader>
                    <CardTitle>Presupuesto #{{ budget.id }}</CardTitle>
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

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <Label for="amount">Monto</Label>
                                <Input
                                    id="amount"
                                    v-model.number="form.amount"
                                    type="number"
                                    step="0.01"
                                    class="mt-1"
                                    :class="{ 'border-red-500': errors.amount }"
                                />
                                <p v-if="errors.amount" class="mt-1 text-sm text-red-600">{{ errors.amount }}</p>
                            </div>

                            <div>
                                <Label for="year">Año</Label>
                                <Input
                                    id="year"
                                    v-model.number="form.year"
                                    type="number"
                                    class="mt-1"
                                    :class="{ 'border-red-500': errors.year }"
                                />
                                <p v-if="errors.year" class="mt-1 text-sm text-red-600">{{ errors.year }}</p>
                            </div>

                            <div>
                                <Label for="month">Mes</Label>
                                <select
                                    id="month"
                                    v-model.number="form.month"
                                    class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2"
                                >
                                    <option v-for="m in 12" :key="m" :value="m">
                                        {{ String(m).padStart(2, '0') }}
                                    </option>
                                </select>
                                <p v-if="errors.month" class="mt-1 text-sm text-red-600">{{ errors.month }}</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <Button type="submit">Guardar Cambios</Button>
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
