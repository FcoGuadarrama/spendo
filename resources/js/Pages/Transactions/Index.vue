<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table'
import {
    Pagination,
    PaginationEllipsis,
    PaginationFirst,
    PaginationLast,
    PaginationNext,
    PaginationPrev,
    PaginationList,
    PaginationListItem,
} from '@/Components/ui/pagination'
import { Badge } from '@/Components/ui/badge'
import { PlusIcon, TrendingDownIcon, TrendingUpIcon, ArrowUpIcon, ArrowDownIcon, EditIcon, TrashIcon } from 'lucide-vue-next'
import {toast} from "@/Components/ui/toast";
import DeleteButton from "@/Components/DeleteButton.vue";

interface Account {
    id: number
    name: string
    type: string
    balance: number
    currency: string
}

interface Category {
    id: number
    name: string
    type: string
    color: string
}

interface Transaction {
    id: number
    type: 'income' | 'expense' | 'transfer'
    amount: number
    description: string
    date: string
    is_confirmed: boolean
    account: Account
    category: Category | null
}

interface PaginatedResponse<T> {
    data: T[]
    current_page: number
    per_page: number
    total: number
    last_page: number
}

const page = usePage()

const props = defineProps<{
    transactions: PaginatedResponse<Transaction>
    accounts: Account[]
    categories: Category[]
    filters: {
        search?: string
        type?: string
        account_id?: number
        category_id?: number
        date_from?: string
        date_to?: string
    }
}>()

const search = computed({
    get: () => props.filters.search ?? '',
    set: (value) => updateFilters({ search: value }),
})

const typeFilter = computed({
    get: () => props.filters.type ?? '',
    set: (value) => updateFilters({ type: value || undefined }),
})

const accountFilter = computed({
    get: () => props.filters.account_id ?? '',
    set: (value) => updateFilters({ account_id: value ? parseInt(value as any) : undefined }),
})

const updateFilters = (newFilters: any) => {
    router.get(route('transactions.index'), {
        ...props.filters,
        ...newFilters,
    }, {
        preserveState: true,
        replace: true,
    })
}

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
    }).format(amount)
}

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-MX', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    })
}

const getTypeIcon = (type: string) => {
    return type === 'income' ? TrendingUpIcon : TrendingDownIcon
}

const getTypeColor = (type: string) => {
    switch (type) {
        case 'income':
            return 'text-green-600'
        case 'expense':
            return 'text-red-600'
        case 'transfer':
            return 'text-blue-600'
        default:
            return ''
    }
}

const getTypeLabel = (type: string) => {
    switch (type) {
        case 'income':
            return 'Ingreso'
        case 'expense':
            return 'Gasto'
        case 'transfer':
            return 'Transferencia'
        default:
            return type
    }
}

const confirmDelete = (transactionId: number) => {
    router.delete(route('transactions.destroy', transactionId), {
        onSuccess: () => {
            toast({ title: 'Éxito', description: 'Transacción eliminada exitosamente' })
        },
        onError: () => {
            toast({ title: 'Error', description: 'No se pudo eliminar la transacción', variant: 'destructive' })
        },
    })
}
</script>

<template>
    <Head title="Transacciones" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Transacciones
                </h2>
                <Link :href="route('transactions.create')">
                    <Button>
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Nueva Transacción
                    </Button>
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle>Filtros</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="text-sm font-medium">Buscar</label>
                            <Input
                                v-model="search"
                                type="text"
                                placeholder="Descripción..."
                                class="mt-1 w-full h-4 rounded-md border border-input bg-background px-3 py-5"
                            />
                        </div>
                        <div>
                            <label class="text-sm font-medium">Tipo</label>
                            <select
                                v-model="typeFilter"
                                class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2"
                            >
                                <option value="">Todos</option>
                                <option value="income">Ingreso</option>
                                <option value="expense">Gasto</option>
                                <option value="transfer">Transferencia</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium">Cuenta</label>
                            <select
                                v-model="accountFilter"
                                class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2"
                            >
                                <option value="">Todas</option>
                                <option
                                    v-for="account in accounts"
                                    :key="account.id"
                                    :value="account.id"
                                >
                                    {{ account.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Transactions Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Registro de Transacciones</CardTitle>
                    <CardDescription>
                        {{ transactions.total }} transacciones encontradas
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Fecha</TableHead>
                                    <TableHead>Descripción</TableHead>
                                    <TableHead>Tipo</TableHead>
                                    <TableHead>Cuenta</TableHead>
                                    <TableHead>Categoría</TableHead>
                                    <TableHead class="text-right">Monto</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead>Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow
                                    v-for="transaction in transactions.data"
                                    :key="transaction.id"
                                >
                                    <TableCell class="text-sm">
                                        {{ formatDate(transaction.date) }}
                                    </TableCell>
                                    <TableCell class="text-sm">
                                        {{ transaction.description }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <component
                                                :is="getTypeIcon(transaction.type)"
                                                class="h-4 w-4"
                                            />
                                            <span class="text-sm" :class="getTypeColor(transaction.type)">
                                                {{ getTypeLabel(transaction.type) }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-sm">
                                        {{ transaction.account.name }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            v-if="transaction.category"
                                            variant="outline"
                                        >
                                            {{ transaction.category.name }}
                                        </Badge>
                                        <span v-else class="text-sm text-muted-foreground">—</span>
                                    </TableCell>
                                    <TableCell class="text-right font-semibold">
                                        <span :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                            {{ transaction.type === 'income' ? '+' : '-' }}
                                            {{ formatCurrency(transaction.amount) }}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            v-if="transaction.is_confirmed"
                                            variant="outline"
                                        >
                                            Confirmado
                                        </Badge>
                                        <Badge v-else variant="secondary">
                                            Pendiente
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex gap-2">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                as-child
                                            >
                                                <Link :href="route('transactions.edit', transaction.id)">
                                                    <EditIcon class="h-4 w-4" />
                                                </Link>
                                            </Button>
                                           <DeleteButton
                                               @delete="confirmDelete(transaction.id)"
                                           ></DeleteButton>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        <div v-if="transactions.data.length === 0" class="py-8 text-center text-muted-foreground">
                            No hay transacciones registradas
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="transactions.last_page > 1" class="mt-6 flex justify-center">
                        <Pagination
                            :items-per-page="transactions.per_page"
                            :total="transactions.total"
                            :sibling-count="1"
                            :default-page="transactions.current_page"
                            @update:page="(page) => updateFilters({ page })"
                        >
                            <PaginationFirst
                                v-if="transactions.current_page > 1"
                                as-child
                            >
                                <Link :href="route('transactions.index', { page: 1, ...props.filters })" />
                            </PaginationFirst>
                            <PaginationPrev
                                v-if="transactions.current_page > 1"
                                as-child
                            >
                                <Link :href="route('transactions.index', { page: transactions.current_page - 1, ...props.filters })" />
                            </PaginationPrev>

                            <PaginationList v-slot="{ items }">
                                <PaginationListItem
                                    v-for="(item, index) in items"
                                    :key="index"
                                    :value="item.type === 'page' ? item.value : index"
                                >
                                    <Link
                                        v-if="item.type === 'page'"
                                        :href="route('transactions.index', { page: item.value, ...props.filters })"
                                        :class="item.value === transactions.current_page ? 'font-bold' : ''"
                                    >
                                        {{ item.value }}
                                    </Link>
                                    <PaginationEllipsis v-else />
                                </PaginationListItem>
                            </PaginationList>

                            <PaginationNext
                                v-if="transactions.current_page < transactions.last_page"
                                as-child
                            >
                                <Link :href="route('transactions.index', { page: transactions.current_page + 1, ...props.filters })" />
                            </PaginationNext>
                            <PaginationLast
                                v-if="transactions.current_page < transactions.last_page"
                                as-child
                            >
                                <Link :href="route('transactions.index', { page: transactions.last_page, ...props.filters })" />
                            </PaginationLast>
                        </Pagination>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
