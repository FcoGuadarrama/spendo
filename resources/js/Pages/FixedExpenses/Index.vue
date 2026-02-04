<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table'
import { Plus, Pencil, Trash2, Calendar, DollarSign } from 'lucide-vue-next'
import { formatCurrency } from '@/lib/utils'
import { useToast } from '@/Components/ui/toast/use-toast'

interface FixedExpense {
    id: number
    description: string
    amount: number
    day_of_month: number
    category_id: number | null
    is_active: boolean
    category?: {
        name: string
        color: string
        icon: string
    }
}

const props = defineProps<{
    fixedExpenses: FixedExpense[]
    categories: any[]
}>()

const { toast } = useToast()

const deleteExpense = (id: number) => {
    if (confirm('¿Estás seguro de que deseas eliminar este gasto fijo?')) {
        router.delete(route('fixed-expenses.destroy', id), {
            onSuccess: () => {
                toast({
                    title: 'Gasto eliminado',
                    description: 'El gasto fijo ha sido eliminado correctamente.',
                })
            }
        })
    }
}
</script>

<template>
    <Head title="Gastos Fijos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Gastos Fijos Mensuales
                </h2>
                <Button as-child>
                    <Link :href="route('fixed-expenses.create')">
                        <Plus class="mr-2 h-4 w-4" />
                        Nuevo Gasto
                    </Link>
                </Button>
            </div>
        </template>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mb-6">
            <!-- Summary Card -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Mensual Fijo</CardTitle>
                    <DollarSign class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">
                        {{ formatCurrency(fixedExpenses.reduce((sum, item) => sum + Number(item.amount), 0)) }}
                    </div>
                    <p class="text-xs text-muted-foreground">
                        Suma de todos tus gastos fijos activos
                    </p>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Listado de Gastos Fijos</CardTitle>
                <CardDescription>
                    Estos gastos se agregarán automáticamente a tu proyección de "Compromisos Mensuales".
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Día de Pago</TableHead>
                            <TableHead>Descripción</TableHead>
                            <TableHead>Categoría</TableHead>
                            <TableHead class="text-right">Monto</TableHead>
                            <TableHead class="text-right">Acciones</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="expense in fixedExpenses" :key="expense.id">
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <Calendar class="h-4 w-4 text-muted-foreground" />
                                    <span class="font-medium">Día {{ expense.day_of_month }}</span>
                                </div>
                            </TableCell>
                            <TableCell class="font-medium">{{ expense.description }}</TableCell>
                            <TableCell>
                                <div v-if="expense.category" class="flex items-center gap-2">
                                    <div
                                        class="h-3 w-3 rounded-full"
                                        :style="{ backgroundColor: expense.category.color }"
                                    />
                                    <span>{{ expense.category.name }}</span>
                                </div>
                                <span v-else class="text-muted-foreground italic">Sin categoría</span>
                            </TableCell>
                            <TableCell class="text-right font-bold">
                                {{ formatCurrency(expense.amount) }}
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Button variant="ghost" size="icon" as-child>
                                        <Link :href="route('fixed-expenses.edit', expense.id)">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="ghost" size="icon" class="text-red-600 hover:text-red-700 hover:bg-red-50" @click="deleteExpense(expense.id)">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="fixedExpenses.length === 0">
                            <TableCell colspan="5" class="h-24 text-center text-muted-foreground">
                                No hay gastos fijos registrados. Comienza agregando uno (ej. Renta, Internet).
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    </AuthenticatedLayout>
</template>
