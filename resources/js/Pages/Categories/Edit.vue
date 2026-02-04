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
    category: Category & { type: string; parent_id: number | null; color: string; icon: string; is_active: boolean }
    categories: Category[]
}>()

const form = ref({
    name: props.category.name,
    type: props.category.type,
    parent_id: props.category.parent_id,
    color: props.category.color,
    icon: props.category.icon,
    is_active: props.category.is_active,
})

const errors = ref<Record<string, string>>({})

const submit = () => {
    router.put(route('categories.update', props.category.id), form.value, {
        onSuccess: () => {
            toast({ title: 'Éxito', description: 'Categoria actualizada exitosamente' })
            router.visit(route('categories.index'))
        },
        onError: (pageErrors) => {
            toast({ title: 'Error', description: 'No se pudo editar la categoria', variant: 'destructive' })
            errors.value = pageErrors as Record<string, string>
        },
    })
}
</script>

<template>
    <Head :title="`Editar Categoría: ${category.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Editar Categoría
            </h2>
        </template>

        <div class="mx-auto max-w-2xl">
            <Card>
                <CardHeader>
                    <CardTitle>{{ category.name }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="name">Nombre</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
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
                                        v-for="cat in categories"
                                        :key="cat.id"
                                        :value="cat.id"
                                    >
                                        {{ cat.name }}
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
                                    class="mt-1"
                                />
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300"
                            />
                            <Label for="is_active" class="mb-0 cursor-pointer">
                                Categoría activa
                            </Label>
                        </div>

                        <div class="flex gap-4">
                            <Button type="submit">Guardar Cambios</Button>
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
