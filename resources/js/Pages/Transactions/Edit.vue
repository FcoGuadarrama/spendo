<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'

interface Account {
    id: number
    name: string
}

interface Category {
    id: number
    name: string
}

interface Transaction {
    id: number
    type: string
    amount: number
    description: string
    date: string
    account_id: number
    category_id: number | null
    transfer_to_account_id: number | null
    debt_id: number | null
    notes: string
    is_confirmed: boolean
}

const props = defineProps<{
    transaction: Transaction
    accounts: Account[]
    categories: Category[]
    debts: { id: number; name: string }[]
}>()

const form = ref({
    type: props.transaction.type,
    amount: props.transaction.amount,
    description: props.transaction.description,
    date: props.transaction.date,
    account_id: props.transaction.account_id,
    category_id: props.transaction.category_id,
    transfer_to_account_id: props.transaction.transfer_to_account_id,
    debt_id: props.transaction.debt_id || null, // Assuming debt_id is available in transaction prop. We need to check interface.
    notes: props.transaction.notes,
    is_confirmed: props.transaction.is_confirmed,
})

const errors = ref<Record<string, string>>({})

const submit = () => {
    router.put(route('transactions.update', props.transaction.id), form.value, {
        onError: (pageErrors) => {
            errors.value = pageErrors as Record<string, string>
        },
    })
}
</script>

<template>
    <Head :title="`Editar Transacción #${transaction.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Editar Transacción
            </h2>
        </template>

        <div class="mx-auto max-w-2xl">
            <Card>
                <CardHeader>
                    <CardTitle>Transacción #{{ transaction.id }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
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
                                    <option value="transfer">Transferencia</option>
                                </select>
                                <p v-if="errors.type" class="mt-1 text-sm text-red-600">{{ errors.type }}</p>
                            </div>

                            <div>
                                <Label for="amount">Monto</Label>
                                <Input
                                    id="amount"
                                    v-model.number="form.amount"
                                    type="number"
                                    step="0.01"
                                    class="mt-1"
                                    :class="{ 'border-red-500': errors.amount }"
                                />
                                <p v-if="errors.amount" class="mt-1 text-sm text-red-600">{{ errors.amount }}</p>
                            </div>
                        </div>

                        <div>
                            <Label for="description">Descripción</Label>
                            <Input
                                id="description"
                                v-model="form.description"
                                type="text"
                                class="mt-1"
                                :class="{ 'border-red-500': errors.description }"
                            />
                            <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label for="account_id">Cuenta</Label>
                                <select
                                    id="account_id"
                                    v-model="form.account_id"
                                    class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2"
                                >
                                    <option v-for="account in accounts" :key="account.id" :value="account.id">
                                        {{ account.name }}
                                    </option>
                                </select>
                                <p v-if="errors.account_id" class="mt-1 text-sm text-red-600">{{ errors.account_id }}</p>
                            </div>

                            <div>
                                <Label for="category_id">Categoría</Label>
                                <select
                                    id="category_id"
                                    v-model="form.category_id"
                                    class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2"
                                >
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <p v-if="errors.category_id" class="mt-1 text-sm text-red-600">{{ errors.category_id }}</p>
                            </div>
                        </div>

                        <div v-if="form.type === 'expense'" class="p-4 bg-muted/50 rounded-lg space-y-2">
                             <Label for="debt_id">¿Es pago de una deuda?</Label>
                             <select
                                 id="debt_id"
                                 v-model="form.debt_id"
                                 class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2"
                             >
                                 <option :value="null">No, es un gasto regular</option>
                                 <option v-for="debt in debts" :key="debt.id" :value="debt.id">
                                     {{ debt.name }}
                                 </option>
                             </select>
                         </div>

                        <div>
                            <Label for="date">Fecha</Label>
                            <Input
                                id="date"
                                v-model="form.date"
                                type="date"
                                class="mt-1"
                                :class="{ 'border-red-500': errors.date }"
                            />
                            <p v-if="errors.date" class="mt-1 text-sm text-red-600">{{ errors.date }}</p>
                        </div>

                        <div>
                            <Label for="notes">Notas (Opcional)</Label>
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2"
                                rows="3"
                            />
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                id="is_confirmed"
                                v-model="form.is_confirmed"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300"
                            />
                            <Label for="is_confirmed" class="mb-0 cursor-pointer">
                                Transacción confirmada
                            </Label>
                        </div>

                        <div class="flex gap-4">
                            <Button type="submit">Guardar Cambios</Button>
                            <Button type="button" variant="outline" as-child>
                                <Link :href="route('transactions.index')">Cancelar</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
