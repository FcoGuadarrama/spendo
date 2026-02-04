<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
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
    TargetIcon,
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

interface Debt {
    id: number
    name: string
    total_amount: number
    remaining_amount: number
    monthly_payment: number | null
    type: string
}

interface MonthlyTrend {
    month: string
    year: number
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
    debts: Debt[]
    totalPersonalDebt: number
    totalPersonalMonthly: number
    totalCCDebt: number
    totalCCMonthly: number
    monthlyCalendar: any[] // Using any for simplicity as it's a mixed object
    totalMonthlyCommitment: number
    debugTotalFixed: number
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
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
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

                    <!-- Monthly Commitments (Fixed Expenses + Debts) -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Compromisos Fijos</CardTitle>
                            <TargetIcon class="h-4 w-4 text-orange-500" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-orange-600">{{ formatCurrency(totalMonthlyCommitment) }}</div>
                            <p class="text-xs text-muted-foreground">
                                Gastos Fijos ({{ formatCurrency(debugTotalFixed) }}) + Deudas
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

                    <!-- Debts -->
                    <div class="space-y-6">
                        <!-- Credit Card MSI Summary -->
                         <Card v-if="totalCCDebt > 0">
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <CardTitle>Tarjetas de Crédito (MSI)</CardTitle>
                                        <Badge variant="secondary">Meses sin Intereses</Badge>
                                    </div>
                                    <CreditCardIcon class="h-4 w-4 text-muted-foreground" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <p class="text-xs font-medium text-muted-foreground">Deuda Total MSI</p>
                                        <p class="text-xl font-bold">{{ formatCurrency(totalCCDebt) }}</p>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-xs font-medium text-muted-foreground">Pago Mensual Total</p>
                                        <p class="text-xl font-bold text-orange-600">{{ formatCurrency(totalCCMonthly) }}</p>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-sm font-medium text-muted-foreground">Gastos Proyectados</p>
                                        <p class="text-xl font-bold text-blue-600">{{ formatCurrency(totalMonthlyCommitment - totalPersonalMonthly - totalCCMonthly) }}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <CardTitle>Deudas Personales</CardTitle>
                                    <Link :href="route('debts.index')">
                                        <Button variant="ghost" size="sm">Ver todas</Button>
                                    </Link>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4 pb-2">
                                        <div class="space-y-1">
                                            <p class="text-xs font-medium text-muted-foreground">Total Deuda</p>
                                            <p class="text-xl font-bold">{{ formatCurrency(totalPersonalDebt) }}</p>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-xs font-medium text-muted-foreground">Mensualidad Total</p>
                                            <p class="text-xl font-bold">{{ formatCurrency(totalPersonalMonthly) }}</p>
                                        </div>
                                    </div>
                                    <div
                                        v-for="debt in debts.slice(0, 5)"
                                        :key="debt.id"
                                        class="space-y-2"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium">{{ debt.name }}</span>
                                                <Badge v-if="debt.type === 'credit_card'" variant="outline" class="text-[10px] h-5">MSI</Badge>
                                            </div>
                                            <span class="text-sm font-semibold">{{ formatCurrency(debt.remaining_amount) }}</span>
                                        </div>
                                        <Progress :model-value="((debt.total_amount - debt.remaining_amount) / debt.total_amount) * 100" class="h-2" />
                                        <div class="flex justify-between text-xs text-muted-foreground">
                                            <span>Total: {{ formatCurrency(debt.total_amount) }}</span>
                                            <span v-if="debt.monthly_payment">
                                                Pago: {{ formatCurrency(debt.monthly_payment) }}/mes
                                            </span>
                                        </div>
                                    </div>
                                    <div
                                        v-if="debts.length === 0"
                                        class="py-8 text-center text-muted-foreground"
                                    >
                                        ¡Libre de deudas! 🎉
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Monthly Payment Calendar -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <CardTitle>Agenda de Pagos</CardTitle>
                                <Link :href="route('fixed-expenses.index')">
                                    <Badge variant="outline" class="cursor-pointer hover:bg-muted">Administrar</Badge>
                                </Link>
                            </div>
                            <CardDescription>Proyección de pagos basada en tus fechas de corte y gastos fijos.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="item in monthlyCalendar" :key="item.id + item.type" class="flex items-center justify-between border-b pb-2 last:border-0">
                                    <div class="flex items-center gap-3">
                                        <div class="flex flex-col items-center justify-center w-10 h-10 rounded-md bg-muted">
                                            <span class="text-xs font-bold text-muted-foreground">DIA</span>
                                            <span class="text-sm font-bold">{{ item.day }}</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium leading-none">{{ item.name }}</p>
                                            <p class="text-xs text-muted-foreground capitalize">
                                                {{ item.type === 'fixed_expense' ? 'Gasto Fijo' : item.type.replace('_', ' ') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold">{{ formatCurrency(item.amount) }}</p>
                                        <p class="text-[10px] text-muted-foreground" v-if="item.type === 'credit_card'">
                                            Total MSI
                                        </p>
                                    </div>
                                </div>
                                <div v-if="monthlyCalendar.length === 0" class="py-6 text-center text-muted-foreground">
                                    No hay pagos proyectados para este mes.
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
