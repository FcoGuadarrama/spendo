<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
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
import { Progress } from '@/Components/ui/progress'
import { PlusIcon, WalletIcon, DollarSignIcon, EditIcon, CreditCardIcon } from 'lucide-vue-next'
import { toast } from '@/Components/ui/toast/use-toast'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DeleteButton from "@/Components/DeleteButton.vue";

interface CreditCardData {
    credit_limit: number
    statement_balance: number
    available_credit: number
    credit_utilization: number
    monthly_payment: number
    total_debt: number
    due_day: number | null
    closing_day: number | null
}

interface Account {
    id: number
    name: string
    type: string
    balance: number
    currency: string
    color: string
    icon: string
    is_active: boolean
    transactions_count: number
    credit_limit?: number
    statement_balance?: number
    available_credit?: number
    credit_utilization?: number
    monthly_payment?: number
    total_debt?: number
    due_day?: number | null
    closing_day?: number | null
}

const props = defineProps<{
    accounts: Account[]
}>()

const creditCards = computed(() => props.accounts.filter(a => a.type === 'credit_card'))
const otherAccounts = computed(() => props.accounts.filter(a => a.type !== 'credit_card'))

const totalBalance = computed(() => {
    return otherAccounts.value.reduce((sum, a) => sum + Number(a.balance || 0), 0)
})

const totalCreditDebt = computed(() => {
    return creditCards.value.reduce((sum, a) => sum + Number(a.total_debt || 0), 0)
})

const totalMonthlyPayment = computed(() => {
    return creditCards.value.reduce((sum, a) => sum + Number(a.monthly_payment || 0), 0)
})

const totalTransactions = computed(() => {
    return props.accounts.reduce((sum, a) => sum + Number(a.transactions_count || 0), 0)
})

const activeAccountsCount = computed(() => {
    return props.accounts.filter((a) => a.is_active).length
})

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(amount)
}

const getTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        checking: 'Cuenta Corriente',
        savings: 'Ahorros',
        credit_card: 'Tarjeta de Crédito',
        cash: 'Efectivo',
        investment: 'Inversión',
    }
    return labels[type] || type
}

const confirmDelete = (accountId: number) => {
    router.delete(route('accounts.destroy', accountId), {
        onSuccess: () => {
            toast({ title: 'Éxito', description: 'Cuenta eliminada exitosamente' })
        },
        onError: () => {
            toast({ title: 'Error', description: 'No se pudo eliminar la cuenta', variant: 'destructive' })
        },
    })
}

const getUtilizationColor = (utilization: number) => {
    if (utilization > 90) return 'text-red-600'
    if (utilization > 70) return 'text-orange-600'
    return 'text-green-600'
}
</script>

<template>
    <AuthenticatedLayout>
    <Head title="Cuentas" />

    <template #header>
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Cuentas
            </h2>
            <Link :href="route('accounts.create')">
                    <Button>
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Nueva Cuenta
                    </Button>
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Summary Stats -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total de Cuentas</CardTitle>
                        <WalletIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ accounts.length }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ activeAccountsCount }} activas
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Balance Total (Cuentas)</CardTitle>
                        <DollarSignIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ formatCurrency(totalBalance) }}
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Transacciones</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ totalTransactions }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Credit Cards Summary -->
            <div v-if="creditCards.length > 0" class="grid gap-4 md:grid-cols-3">
                <Card class="border-orange-200 dark:border-orange-900">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Deuda Total (TDC)</CardTitle>
                        <CreditCardIcon class="h-4 w-4 text-orange-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-orange-600">
                            {{ formatCurrency(totalCreditDebt) }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Incluyendo MSI activos
                        </p>
                    </CardContent>
                </Card>
                <Card class="border-blue-200 dark:border-blue-900">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pago Mensual Total (TDC)</CardTitle>
                        <DollarSignIcon class="h-4 w-4 text-blue-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">
                            {{ formatCurrency(totalMonthlyPayment) }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Suma de todos los MSI
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Tarjetas de Crédito</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ creditCards.length }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Activas
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Credit Cards Detailed View -->
            <div v-if="creditCards.length > 0" class="space-y-4">
                <h3 class="text-lg font-semibold">Tarjetas de Crédito Detalladas</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <Card v-for="card in creditCards" :key="card.id" class="border-l-4 border-orange-500">
                        <CardHeader class="pb-3">
                            <div class="flex items-start justify-between">
                                <div>
                                    <CardTitle class="text-base">{{ card.name }}</CardTitle>
                                    <CardDescription class="mt-1 flex gap-2">
                                        <Badge v-if="card.is_active" variant="outline" class="text-xs">Activa</Badge>
                                        <Badge v-else variant="secondary" class="text-xs">Inactiva</Badge>
                                    </CardDescription>
                                </div>
                                <Link :href="route('accounts.edit', card.id)">
                                    <Button size="icon" variant="ghost" class="h-8 w-8">
                                        <EditIcon class="h-4 w-4" />
                                    </Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Limit & Usage -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Límite de Crédito</span>
                                    <span class="font-semibold">{{ formatCurrency(card.credit_limit) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Utilización</span>
                                    <span :class="['font-semibold', getUtilizationColor(card.credit_utilization)]">
                                        {{ card.credit_utilization.toFixed(1) }}%
                                    </span>
                                </div>
                                <Progress :model-value="card.credit_utilization" class="h-2" />
                            </div>

                            <!-- Debt Information -->
                            <div class="border-t pt-3 space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Deuda Total</span>
                                    <span class="font-bold text-orange-600">{{ formatCurrency(card.total_debt) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Crédito Disponible</span>
                                    <span class="font-bold text-green-600">{{ formatCurrency(card.available_credit) }}</span>
                                </div>
                            </div>

                            <!-- Payment Information -->
                            <div class="border-t pt-3 space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Pago Mensual (MSI)</span>
                                    <span class="font-bold text-blue-600">{{ formatCurrency(card.monthly_payment) }}</span>
                                </div>
                                <div v-if="card.closing_day || card.due_day" class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Ciclo de Facturación</span>
                                    <span class="text-xs">
                                        <span v-if="card.closing_day">Cierre: Día {{ card.closing_day }}</span>
                                        <span v-if="card.closing_day && card.due_day"> | </span>
                                        <span v-if="card.due_day">Pago: Día {{ card.due_day }}</span>
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Other Accounts Table -->
            <Card>
                <CardHeader>
                    <CardTitle>{{ otherAccounts.length > 0 ? 'Otras Cuentas' : 'Mis Cuentas' }}</CardTitle>
                    <CardDescription>
                        Gestiona tus cuentas y visualiza sus saldos
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nombre</TableHead>
                                    <TableHead>Tipo</TableHead>
                                    <TableHead>Transacciones</TableHead>
                                    <TableHead class="text-right">Balance</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead class="text-right">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="account in otherAccounts"
                                    :key="account.id"
                                >
                                    <TableCell class="font-medium">{{ account.name }}</TableCell>
                                    <TableCell class="text-sm text-muted-foreground">
                                        {{ getTypeLabel(account.type) }}
                                    </TableCell>
                                    <TableCell class="text-sm">
                                        {{ account.transactions_count }}
                                    </TableCell>
                                    <TableCell class="text-right font-semibold">
                                        {{ formatCurrency(account.balance) }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge v-if="account.is_active" variant="outline">
                                            Activa
                                        </Badge>
                                        <Badge v-else variant="secondary">
                                            Inactiva
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('accounts.edit', account.id)">
                                                <Button size="sm" variant="outline">
                                                    <EditIcon class="h-4 w-4" />
                                                </Button>
                                            </Link>
                                            <DeleteButton
                                                @delete="confirmDelete(account.id)"
                                            ></DeleteButton>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        <div v-if="otherAccounts.length === 0" class="py-8 text-center text-muted-foreground">
                            No tienes otras cuentas configuradas.
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
