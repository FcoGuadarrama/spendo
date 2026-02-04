<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import {toast} from "@/Components/ui/toast";

interface Account {
    id: number
    name: string
}

interface Category {
    id: number
    name: string
}

const props = defineProps<{
    accounts: Account[]
    categories: Category[]
    debts: { id: number; name: string }[]
}>()

const form = ref({
    account_id: props.accounts[0]?.id || null,
    category_id: null as number | null,
    transfer_to_account_id: null as number | null,
    debt_id: null as number | null,
    type: 'expense',
    amount: 0,
    description: '',
    notes: '',
    date: new Date().toISOString().split('T')[0],
    time: new Date().toTimeString().slice(0, 5),
    is_confirmed: true,
    is_recurring: false,
})

const errors = ref<Record<string, string>>({})

const submit = () => {
    router.post(route('transactions.store'), form.value, {
        onSuccess: () => {
            toast({ title: 'Transacción creada', description: 'La transacción ha sido creada exitosamente' })
        },
        onError: (pageErrors) => {
            errors.value = pageErrors as Record<string, string>
            toast({ title: 'Error al crear la transacción', description: 'Por favor verifica los datos e intenta nuevamente', variant: 'destructive' })
        },
    })
}
</script>

<template>
    <Head title="Nueva Transacción" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Nueva Transacción
            </h2>
        </template>

        <div class="mx-auto max-w-2xl">
            <Card>
                <CardHeader>
                    <CardTitle>Registrar una transacción</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
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

                            <div v-if="form.type !== 'transfer'">
                                <Label for="category_id">Categoría (Opcional)</Label>
                                <select
                                    id="category_id"
                                    v-model="form.category_id"
                                    class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2"
                                >
                                    <option :value="null">Sin categoría</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>

                            <div v-else>
                                <Label for="transfer_to_account_id">Transferir a</Label>
                                <select
                                    id="transfer_to_account_id"
                                    v-model="form.transfer_to_account_id"
                                    class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2"
                                >
                                    <option :value="null">Selecciona una cuenta</option>
                                    <option
                                        v-for="account in accounts.filter((a) => a.id !== form.account_id)"
                                        :key="account.id"
                                        :value="account.id"
                                    >
                                        {{ account.name }}
                                    </option>
                                </select>
                                <p v-if="errors.transfer_to_account_id" class="mt-1 text-sm text-red-600">
                                    {{ errors.transfer_to_account_id }}
                                </p>
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
                             <p class="text-xs text-muted-foreground mt-1" v-if="form.debt_id">
                                 Este pago se descontará de la deuda seleccionada.
                             </p>
                         </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label for="amount">Monto</Label>
                                <Input
                                    id="amount"
                                    v-model.number="form.amount"
                                    type="number"
                                    placeholder="0.00"
                                    step="0.01"
                                    min="0.01"
                                    class="mt-1"
                                    :class="{ 'border-red-500': errors.amount }"
                                />
                                <p v-if="errors.amount" class="mt-1 text-sm text-red-600">{{ errors.amount }}</p>
                            </div>

                            <div>
                                <Label for="description">Descripción</Label>
                                <Input
                                    id="description"
                                    v-model="form.description"
                                    type="text"
                                    placeholder="Descripción"
                                    class="mt-1"
                                    :class="{ 'border-red-500': errors.description }"
                                />
                                <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
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
                                <Label for="time">Hora (Opcional)</Label>
                                <Input
                                    id="time"
                                    v-model="form.time"
                                    type="time"
                                    class="mt-1"
                                />
                            </div>
                        </div>

                        <div>
                            <Label for="notes">Notas (Opcional)</Label>
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2"
                                rows="3"
                                placeholder="Notas adicionales..."
                            />
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                id="is_recurring"
                                v-model="form.is_recurring"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300"
                            />
                            <Label for="is_recurring" class="mb-0 cursor-pointer">
                                Gasto Recurrente (se paga mensualmente)
                            </Label>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                id="is_confirmed"
                                v-model="form.is_confirmed"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300"
                            />
                            <Label for="is_confirmed" class="mb-0 cursor-pointer">
                                Marcar como confirmado
                            </Label>
                        </div>

                        <div class="flex gap-4">
                            <Button type="submit">Crear Transacción</Button>
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
