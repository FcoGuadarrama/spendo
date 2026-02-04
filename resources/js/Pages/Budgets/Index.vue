<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Progress } from '@/Components/ui/progress'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table'
import { PlusIcon, TargetIcon, AlertCircleIcon, EditIcon, TrashIcon } from 'lucide-vue-next'
import DeleteButton from "@/Components/DeleteButton.vue";
import {toast} from "@/Components/ui/toast";

interface Budget {
    id: number
    category_id: number
    category_name: string
    category_color: string
    category_icon: string
    amount: number
    spent: number
    remaining: number
    percentage: number
    status: 'ok' | 'warning' | 'over'
    is_active: boolean
}

const props = defineProps<{
    budgets: Budget[]
    categories: Array<{ id: number; name: string }>
    year: number
    month: number
}>()

const monthName = computed(() => {
    const date = new Date(props.year, props.month - 1)
    return date.toLocaleDateString('es-MX', { month: 'long', year: 'numeric' })
})

const totalBudgeted = computed(() => props.budgets.reduce((sum, b) => sum + b.amount, 0))
const totalSpent = computed(() => props.budgets.reduce((sum, b) => sum + b.spent, 0))
const totalRemaining = computed(() => totalBudgeted.value - totalSpent.value)
const averageSpentPercentage = computed(() => {
    if (props.budgets.length === 0) return 0
    return Math.round(
        props.budgets.reduce((sum, b) => sum + b.percentage, 0) / props.budgets.length
    )
})

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(amount)
}

const getStatusColor = (status: string) => {
    switch (status) {
        case 'ok':
            return 'text-green-600'
        case 'warning':
            return 'text-yellow-600'
        case 'over':
            return 'text-red-600'
        default:
            return ''
    }
}

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'ok':
            return 'Dentro del presupuesto'
        case 'warning':
            return 'Próximo al límite'
        case 'over':
            return 'Excedido'
        default:
            return status
    }
}

const confirmDelete = (budgetId: number) => {
    router.delete(route('budgets.destroy', budgetId), {
        onSuccess: () => {
            toast({ title: 'Éxito', description: 'Presupuesto eliminada exitosamente' })
        },
        onError: () => {
            toast({ title: 'Error', description: 'No se pudo eliminar el presupuesto', variant: 'destructive' })
        },
    })
}
</script>

<template>
    <AuthenticatedLayout>
    <Head title="Presupuestos" />

    <template #header>
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Presupuestos
                </h2>
                <Link :href="route('budgets.create')">
                    <Button>
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Nuevo Presupuesto
                    </Button>
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Month Info -->
            <Card>
                <CardHeader>
                    <CardTitle>{{ monthName }}</CardTitle>
                </CardHeader>
            </Card>

            <!-- Summary Stats -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Presupuestado</CardTitle>
                        <TargetIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatCurrency(totalBudgeted) }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Gastado</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">{{ formatCurrency(totalSpent) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ averageSpentPercentage }}% en promedio
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Disponible</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div
                            class="text-2xl font-bold"
                            :class="totalRemaining >= 0 ? 'text-green-600' : 'text-red-600'"
                        >
                            {{ formatCurrency(totalRemaining) }}
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Presupuestos</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ budgets.length }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Budgets Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Desglose por Categoría</CardTitle>
                    <CardDescription>
                        Visualiza el gasto en cada categoría vs. tu presupuesto
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-6">
                        <div
                            v-for="budget in budgets"
                            :key="budget.id"
                            class="space-y-2"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-3 w-3 rounded-full"
                                        :style="{ backgroundColor: budget.category_color }"
                                    />
                                    <div>
                                        <p class="font-medium">{{ budget.category_name }}</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ formatCurrency(budget.spent) }} / {{ formatCurrency(budget.amount) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-2">
                                        <div :class="getStatusColor(budget.status)">
                                            <span class="font-semibold">{{ budget.percentage }}%</span>
                                        </div>
                                        <Badge v-if="budget.status === 'over'" variant="destructive">
                                            <AlertCircleIcon class="mr-1 h-3 w-3" />
                                            Excedido
                                        </Badge>
                                        <Badge v-else-if="budget.status === 'warning'" variant="outline">
                                            Advertencia
                                        </Badge>
                                    </div>
                                    <div class="flex gap-2">
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            as-child
                                        >
                                            <Link :href="route('budgets.edit', budget.id)">
                                                <EditIcon class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <DeleteButton
                                            @delete="confirmDelete(budget.id)"
                                        ></DeleteButton>
                                    </div>
                                </div>
                            </div>
                            <Progress :model-value="budget.percentage" class="h-2" />
                        </div>
                        <div v-if="budgets.length === 0" class="py-8 text-center text-muted-foreground">
                            No tienes presupuestos para este mes
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
