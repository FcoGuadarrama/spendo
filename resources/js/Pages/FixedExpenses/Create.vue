<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { useToast } from '@/Components/ui/toast/use-toast'
import {Checkbox} from "@/Components/ui/checkbox";

interface Category {
    id: number
    name: string
}

const props = defineProps<{
    categories: Category[]
}>()

const { toast } = useToast()

const form = ref({
    description: '',
    amount: 0,
    day_of_month: 1,
    category_id: null as number | null,
    is_active: true,
})

const errors = ref<Record<string, string>>({})

const submit = () => {
    router.post(route('fixed-expenses.store'), form.value, {
        onError: (pageErrors) => {
            errors.value = pageErrors as Record<string, string>
            toast({
                title: 'Error',
                description: 'Por favor corrige los errores en el formulario.',
                variant: 'destructive',
            })
        },
    })
}
</script>

<template>
    <Head title="Nuevo Gasto Fijo" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Nuevo Gasto Fijo
            </h2>
        </template>

        <div class="mx-auto max-w-2xl py-6">
            <Card>
                <CardHeader>
                    <CardTitle>Registrar Gasto Mensual</CardTitle>
                    <CardDescription>
                        Define un gasto que debas realizar cada mes.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="description">Descripción (ej. Renta, Gimnasio)</Label>
                            <Input
                                id="description"
                                v-model="form.description"
                                type="text"
                                class="mt-1"
                                :class="{ 'border-red-500': errors.description }"
                                placeholder="Nombre del gasto"
                                autofocus
                            />
                            <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label for="amount">Monto Mensual</Label>
                                <Input
                                    id="amount"
                                    v-model.number="form.amount"
                                    type="number"
                                    step="0.01"
                                    class="mt-1"
                                    :class="{ 'border-red-500': errors.amount }"
                                    placeholder="0.00"
                                />
                                <p v-if="errors.amount" class="mt-1 text-sm text-red-600">{{ errors.amount }}</p>
                            </div>

                            <div>
                                <Label for="day_of_month">Día de Pago (1-31)</Label>
                                <Input
                                    id="day_of_month"
                                    v-model.number="form.day_of_month"
                                    type="number"
                                    min="1"
                                    max="31"
                                    class="mt-1"
                                    :class="{ 'border-red-500': errors.day_of_month }"
                                />
                                <p v-if="errors.day_of_month" class="mt-1 text-sm text-red-600">{{ errors.day_of_month }}</p>
                            </div>
                        </div>

                        <div>
                            <Label for="category_id">Categoría (Opcional)</Label>
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            >
                                <option :value="null">Sin categoría</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <p v-if="errors.category_id" class="mt-1 text-sm text-red-600">{{ errors.category_id }}</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <Checkbox :checked="form.is_active" @click="form.is_active = !form.is_active"></Checkbox>
                            <Label for="is_active" class="mb-0 cursor-pointer">Activo</Label>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <Button type="submit">Guardar Gasto</Button>
                            <Button type="button" variant="outline" as-child>
                                <Link :href="route('fixed-expenses.index')">Cancelar</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
