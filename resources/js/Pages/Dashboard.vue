<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Progress } from '@/Components/ui/progress'
import {
    ArrowDownIcon,
    ArrowUpIcon,
    WalletIcon,
    TrendingUpIcon,
    TrendingDownIcon,
    PiggyBankIcon,
    PlusIcon,
    CreditCardIcon,
    BanknoteIcon,
    LandmarkIcon,
} from 'lucide-vue-next'
import { computed } from 'vue'

interface Account {
    id: number
    name: string
    type: string
    balance: number
    currency: string
    color: string
    icon: string
}

interface Transaction {
    id: number
    type: 'income' | 'expense' | 'transfer'
    amount: number
    description: string
    date: string
    account: { name: string }
    category: { name: string; color: string; icon: string } | null
}

interface CategoryExpense {
    category_id: number
    category_name: string
    category_color: string
    total: number
    count: number
}

interface BudgetProgress {
    id: number
    category_name: string
    category_color: string
    amount: number
    spent: number
    percentage: number
    status: 'ok' | 'warning' | 'over'
}

interface MonthlyTrend {
    month: string
    income: number
    expenses: number
}

const props = defineProps<{
    accounts: Account[]
    totalBalance: number
    monthlyIncome: number
    monthlyExpenses: number
    monthlySavings: number
    expensesByCategory: CategoryExpense[]
    recentTransactions: Transaction[]
    budgets: BudgetProgress[]
    monthlyTrend: MonthlyTrend[]
    currentMonth: string
}>()

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
    })
}

const getAccountIcon = (type: string) => {
    switch (type) {
        case 'cash':
            return BanknoteIcon
        case 'credit_card':
            return CreditCardIcon
        default:
            return LandmarkIcon
    }
}

const maxExpense = computed(() => {
    return Math.max(...props.expensesByCategory.map((c) => c.total), 1)
})
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>

        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Dashboard
                </h2>
                <Link :href="route('transactions.create')">
                    <Button>
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Nueva Transacción
                    </Button>
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="space-y-6">
                <!-- Stats Cards -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Balance -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Balance Total</CardTitle>
                            <WalletIcon class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ formatCurrency(totalBalance) }}</div>
                            <p class="text-xs text-muted-foreground">
                                En {{ accounts.length }} cuentas
                            </p>
                        </CardContent>
                    </Card>

                    <!-- Monthly Income -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Ingresos del Mes</CardTitle>
                            <TrendingUpIcon class="h-4 w-4 text-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-green-600">
                                {{ formatCurrency(monthlyIncome) }}
                            </div>
                            <p class="text-xs text-muted-foreground">{{ currentMonth }}</p>
                        </CardContent>
                    </Card>

                    <!-- Monthly Expenses -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Gastos del Mes</CardTitle>
                            <TrendingDownIcon class="h-4 w-4 text-red-500" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-red-600">
                                {{ formatCurrency(monthlyExpenses) }}
                            </div>
                            <p class="text-xs text-muted-foreground">{{ currentMonth }}</p>
                        </CardContent>
                    </Card>

                    <!-- Monthly Savings -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Ahorro del Mes</CardTitle>
                            <PiggyBankIcon class="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div
                                class="text-2xl font-bold"
                                :class="monthlySavings >= 0 ? 'text-blue-600' : 'text-red-600'"
                            >
                                {{ formatCurrency(monthlySavings) }}
                            </div>
                            <p class="text-xs text-muted-foreground">
                                {{ monthlySavings >= 0 ? 'Superávit' : 'Déficit' }}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Accounts -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle>Cuentas</CardTitle>
                                <Link :href="route('accounts.index')">
                                    <Button variant="ghost" size="sm">Ver todas</Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div
                                    v-for="account in accounts"
                                    :key="account.id"
                                    class="flex items-center justify-between"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full"
                                            :style="{ backgroundColor: account.color + '20' }"
                                        >
                                            <component
                                                :is="getAccountIcon(account.type)"
                                                class="h-5 w-5"
                                                :style="{ color: account.color }"
                                            />
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ account.name }}</p>
                                            <p class="text-sm text-muted-foreground">
                                                {{ account.type }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p
                                            class="font-semibold"
                                            :class="account.balance >= 0 ? '' : 'text-red-600'"
                                        >
                                            {{ formatCurrency(account.balance) }}
                                        </p>
                                    </div>
                                </div>
                                <div v-if="accounts.length === 0" class="py-8 text-center text-muted-foreground">
                                    No tienes cuentas configuradas
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Expenses by Category -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle>Gastos por Categoría</CardTitle>
                                <Badge variant="outline">{{ currentMonth }}</Badge>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div
                                    v-for="category in expensesByCategory.slice(0, 5)"
                                    :key="category.category_id"
                                    class="space-y-2"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-3 w-3 rounded-full"
                                                :style="{ backgroundColor: category.category_color }"
                                            />
                                            <span class="text-sm font-medium">{{ category.category_name }}</span>
                                        </div>
                                        <span class="text-sm font-semibold">
                                            {{ formatCurrency(category.total) }}
                                        </span>
                                    </div>
                                    <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                        <div
                                            class="h-full rounded-full transition-all"
                                            :style="{
                                                width: `${(category.total / maxExpense) * 100}%`,
                                                backgroundColor: category.category_color,
                                            }"
                                        />
                                    </div>
                                </div>
                                <div
                                    v-if="expensesByCategory.length === 0"
                                    class="py-8 text-center text-muted-foreground"
                                >
                                    Sin gastos este mes
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Recent Transactions -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle>Transacciones Recientes</CardTitle>
                                <Link :href="route('transactions.index')">
                                    <Button variant="ghost" size="sm">Ver todas</Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div
                                    v-for="transaction in recentTransactions"
                                    :key="transaction.id"
                                    class="flex items-center justify-between"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full"
                                            :class="
                                                transaction.type === 'income'
                                                    ? 'bg-green-100 dark:bg-green-900'
                                                    : 'bg-red-100 dark:bg-red-900'
                                            "
                                        >
                                            <ArrowUpIcon
                                                v-if="transaction.type === 'income'"
                                                class="h-5 w-5 text-green-600"
                                            />
                                            <ArrowDownIcon v-else class="h-5 w-5 text-red-600" />
                                        </div>
                                        <div>
                                            <p class="font-medium">
                                                {{ transaction.description || transaction.category?.name || 'Sin descripción' }}
                                            </p>
                                            <p class="text-sm text-muted-foreground">
                                                {{ transaction.account.name }} · {{ formatDate(transaction.date) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p
                                            class="font-semibold"
                                            :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'"
                                        >
                                            {{ transaction.type === 'income' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-if="recentTransactions.length === 0"
                                    class="py-8 text-center text-muted-foreground"
                                >
                                    No hay transacciones
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Budget Progress -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle>Presupuestos</CardTitle>
                                <Link :href="route('budgets.index')">
                                    <Button variant="ghost" size="sm">Gestionar</Button>
                                </Link>
                            </div>
                            <CardDescription>Progreso de tus presupuestos mensuales</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="budget in budgets" :key="budget.id" class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="h-3 w-3 rounded-full"
                                                :style="{ backgroundColor: budget.category_color }"
                                            />
                                            <span class="text-sm font-medium">{{ budget.category_name }}</span>
                                        </div>
                                        <span class="text-sm text-muted-foreground">
                                            {{ formatCurrency(budget.spent) }} / {{ formatCurrency(budget.amount) }}
                                        </span>
                                    </div>
                                    <Progress
                                        :model-value="budget.percentage"
                                        :class="{
                                            '[&>div]:bg-green-500': budget.status === 'ok',
                                            '[&>div]:bg-yellow-500': budget.status === 'warning',
                                            '[&>div]:bg-red-500': budget.status === 'over',
                                        }"
                                    />
                                </div>
                                <div v-if="budgets.length === 0" class="py-8 text-center text-muted-foreground">
                                    No tienes presupuestos configurados
                                    <Link :href="route('budgets.index')" class="block mt-2 text-primary">
                                        Crear presupuesto
                                    </Link>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
