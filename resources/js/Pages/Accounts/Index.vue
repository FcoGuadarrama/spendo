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
import { PlusIcon, WalletIcon, DollarSignIcon, EditIcon } from 'lucide-vue-next'
import { toast } from '@/Components/ui/toast/use-toast'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DeleteButton from "@/Components/DeleteButton.vue";

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
}

const props = defineProps<{
    accounts: Account[]
}>()

const totalBalance = computed(() => {
    return props.accounts.reduce((sum, a) => sum + Number(a.balance || 0), 0)
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
                        <CardTitle class="text-sm font-medium">Balance Total</CardTitle>
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

            <!-- Accounts Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Mis Cuentas</CardTitle>
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
                                    v-for="account in accounts"
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
                        <div v-if="accounts.length === 0" class="py-8 text-center text-muted-foreground">
                            No tienes cuentas configuradas. Crea una para empezar.
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
