<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Progress } from '@/Components/ui/progress'
import { PlusIcon, EditIcon } from 'lucide-vue-next'
import { toast } from "@/Components/ui/toast"
import DeleteButton from "@/Components/DeleteButton.vue";

interface Debt {
    id: number
    name: string
    total_amount: number
    remaining_amount: number
    paid_percentage: number
    monthly_payment: number | null
    next_payment_date: string | null
}

const props = defineProps<{
    debts: Debt[]
}>()

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(amount)
}

const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A'
    return new Date(dateString).toLocaleDateString('es-MX', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    })
}

const confirmDelete = (debtId: number) => {
    router.delete(route('debts.destroy', debtId), {
        onSuccess: () => {
            toast({ title: 'Éxito', description: 'Deuda eliminada exitosamente' })
        },
        onError: () => {
            toast({ title: 'Error', description: 'No se pudo eliminar la deuda', variant: 'destructive' })
        },
    })
}
</script>

<template>
    <Head title="Deudas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Deudas
                </h2>
                <Link :href="route('debts.create')">
                    <Button>
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Nueva Deuda
                    </Button>
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="debt in debts" :key="debt.id">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            {{ debt.name }}
                        </CardTitle>
                        <div class="flex gap-2">
                            <Link :href="route('debts.edit', debt.id)">
                                <Button variant="ghost" size="icon" class="h-8 w-8">
                                    <EditIcon class="h-4 w-4" />
                                </Button>
                            </Link>
                            <DeleteButton @delete="confirmDelete(debt.id)" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ formatCurrency(debt.remaining_amount) }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            restantes de {{ formatCurrency(debt.total_amount) }}
                        </p>
                        <div class="mt-4 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span>Progreso de pago</span>
                                <span class="font-medium">{{ debt.paid_percentage }}%</span>
                            </div>
                            <Progress :model-value="debt.paid_percentage" class="h-2" />
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-4 text-xs">
                            <div v-if="debt.monthly_payment">
                                <div class="text-muted-foreground">Pago mensual</div>
                                <div class="font-medium">{{ formatCurrency(debt.monthly_payment) }}</div>
                            </div>
                            <div v-if="debt.next_payment_date">
                                <div class="text-muted-foreground">Próximo pago</div>
                                <div class="font-medium">{{ formatDate(debt.next_payment_date) }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="debts.length === 0" class="flex flex-col items-center justify-center rounded-lg border border-dashed p-8 text-center animate-in fade-in-50">
                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-muted">
                    <PlusIcon class="h-6 w-6 text-muted-foreground" />
                </div>
                <h3 class="mt-4 text-lg font-semibold">No tienes deudas registradas</h3>
                <p class="mb-4 text-sm text-muted-foreground">
                    Comienza agregando una nueva deuda para llevar el control.
                </p>
                <Link :href="route('debts.create')">
                    <Button>
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Agregar Deuda
                    </Button>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
