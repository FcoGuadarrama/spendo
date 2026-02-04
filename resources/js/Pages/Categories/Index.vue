<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table'
import { PlusIcon, TagIcon, EditIcon, TrashIcon } from 'lucide-vue-next'
import DeleteButton from "@/Components/DeleteButton.vue";
import {toast} from "@/Components/ui/toast";

interface Category {
    id: number
    name: string
    type: 'income' | 'expense'
    color: string
    icon: string
    is_active: boolean
    children?: Category[]
}

const props = defineProps<{
    categories: Category[]
}>()

const getTypeLabel = (type: string) => {
    return type === 'income' ? 'Ingreso' : 'Gasto'
}

const getTypeColor = (type: string) => {
    return type === 'income' ? 'text-green-600' : 'text-red-600'
}

const confirmDelete = (categoryId: number) => {
    router.delete(route('categories.destroy', categoryId), {
        onSuccess: () => {
            toast({ title: 'Éxito', description: 'Categoria eliminada exitosamente' })
        },
        onError: () => {
            toast({ title: 'Error', description: 'No se pudo eliminar la categoria', variant: 'destructive' })
        },
    })}
</script>

<template>
    <Head title="Categorías" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Categorías
                </h2>
                <Link :href="route('categories.create')">
                    <Button>
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Nueva Categoría
                    </Button>
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Summary Stats -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total de Categorías</CardTitle>
                        <TagIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ categories.length }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Categorías de Ingreso</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">
                            {{ categories.filter((c) => c.type === 'income').length }}
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Categorías de Gasto</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">
                            {{ categories.filter((c) => c.type === 'expense').length }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Categories Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Mis Categorías</CardTitle>
                    <CardDescription>
                        Organiza tus transacciones por categorías
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nombre</TableHead>
                                    <TableHead>Tipo</TableHead>
                                    <TableHead>Subcategorías</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead>Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="category in categories"
                                    :key="category.id"
                                >
                                    <TableCell class="font-medium">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-3 w-3 rounded-full"
                                                :style="{ backgroundColor: category.color }"
                                            />
                                            {{ category.name }}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :class="getTypeColor(category.type)">
                                            {{ getTypeLabel(category.type) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-sm text-muted-foreground">
                                        {{ category.children?.length ?? 0 }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge v-if="category.is_active" variant="outline">
                                            Activa
                                        </Badge>
                                        <Badge v-else variant="secondary">
                                            Inactiva
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex gap-2">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                as-child
                                            >
                                                <Link :href="route('categories.edit', category.id)">
                                                    <EditIcon class="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <DeleteButton
                                                @delete="confirmDelete(category.id)"
                                            ></DeleteButton>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        <div v-if="categories.length === 0" class="py-8 text-center text-muted-foreground">
                            No tienes categorías configuradas. Crea una para empezar.
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
