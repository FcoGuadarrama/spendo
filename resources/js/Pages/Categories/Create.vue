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

const props = defineProps<{
    categories: Category[]
}>()

const form = ref({
    name: '',
    type: 'expense',
    parent_id: null as number | null,
    color: '#ef4444',
    icon: 'tag',
})

const errors = ref<Record<string, string>>({})

const submit = () => {
    router.post(route('categories.store'), form.value, {
        onSuccess: () => {
            toast({ title: 'Categoría creada', description: 'La categoría ha sido creada exitosamente' })
        },
        onError: (pageErrors) => {
            errors.value = pageErrors as Record<string, string>
            toast({ title: 'Error al crear la categoría', description: 'Por favor verifica los datos e intenta nuevamente', variant: 'destructive' })
        },
    })
}
</script>

<template>
    <Head title="Nueva Categoría" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Nueva Categoría
            </h2>
        </template>

        <div class="mx-auto max-w-2xl">
            <Card>
                <CardHeader>
                    <CardTitle>Crear una nueva categoría</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="name">Nombre</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Alimentación"
                                class="mt-1"
                                :class="{ 'border-red-500': errors.name }"
                            />
                            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label for="type">Tipo</Label>
                                <select
                                    id="type"
                                    v-model="form.type"
                                    class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2"
                                >
                                    <option value="income">Ingreso</option>
                                    <option value="expense">Gasto</option>
                                </select>
                                <p v-if="errors.type" class="mt-1 text-sm text-red-600">{{ errors.type }}</p>
                            </div>

                            <div>
                                <Label for="parent_id">Categoría Padre (Opcional)</Label>
                                <select
                                    id="parent_id"
                                    v-model="form.parent_id"
                                    class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2"
                                >
                                    <option :value="null">Sin padre</option>
                                    <option
                                        v-for="category in categories"
                                        :key="category.id"
                                        :value="category.id"
                                    >
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label for="color">Color</Label>
                                <div class="mt-1 flex gap-2">
                                    <Input
                                        id="color"
                                        v-model="form.color"
                                        type="color"
                                        class="h-10 w-16 cursor-pointer"
                                    />
                                    <Input
                                        v-model="form.color"
                                        type="text"
                                        placeholder="#ef4444"
                                        class="flex-1"
                                    />
                                </div>
                            </div>

                            <div>
                                <Label for="icon">Icono</Label>
                                <Input
                                    id="icon"
                                    v-model="form.icon"
                                    type="text"
                                    placeholder="tag"
                                    class="mt-1"
                                />
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <Button type="submit">Crear Categoría</Button>
                            <Button type="button" variant="outline" as-child>
                                <Link :href="route('categories.index')">Cancelar</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
