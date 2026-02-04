<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Textarea } from '@/Components/ui/textarea'
import { ArrowLeftIcon } from 'lucide-vue-next'

interface Debt {
    id: number
    name: string
    total_amount: number
    remaining_amount: number
    monthly_payment: number | null
    start_date: string
    end_date: string | null
    due_day: number | null
    total_installments: number | null
    notes: string | null
    type: string
    account_id: number | null
}

interface Account {
    id: number
    name: string
}

const props = defineProps<{
    debt: Debt
    creditCards: Account[]
}>()

const form = useForm({
    name: props.debt.name,
    total_amount: props.debt.total_amount,
    remaining_amount: props.debt.remaining_amount,
    monthly_payment: props.debt.monthly_payment,
    start_date: props.debt.start_date,
    end_date: props.debt.end_date,
    due_day: props.debt.due_day,
    total_installments: props.debt.total_installments,
    notes: props.debt.notes,
    type: props.debt.type || 'loan',
    account_id: props.debt.account_id,
})

const submit = () => {
    form.put(route('debts.update', props.debt.id))
}
</script>

<template>
    <Head title="Editar Deuda" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('debts.index')">
                    <Button variant="ghost" size="icon">
                        <ArrowLeftIcon class="h-4 w-4" />
                    </Button>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Editar Deuda
                </h2>
            </div>
        </template>

        <div class="mx-auto max-w-2xl">
            <Card>
                <CardHeader>
                    <CardTitle>Editar {{ debt.name }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="type">Tipo de Deuda</Label>
                                <select
                                    id="type"
                                    v-model="form.type"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="loan">Préstamo Personal / Otro</option>
                                    <option value="credit_card">Tarjeta de Crédito (MSI / Diferido)</option>
                                </select>
                                <p v-if="form.errors.type" class="text-sm text-red-500">{{ form.errors.type }}</p>
                            </div>

                            <div v-if="form.type === 'credit_card'" class="space-y-2">
                                <Label for="account_id">Tarjeta de Crédito</Label>
                                <select
                                    id="account_id"
                                    v-model="form.account_id"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option :value="null">Selecciona una tarjeta</option>
                                    <option v-for="card in creditCards" :key="card.id" :value="card.id">
                                        {{ card.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.account_id" class="text-sm text-red-500">{{ form.errors.account_id }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="name">Nombre / Concepto</Label>
                            <Input
                                id="name"
                                type="text"
                                v-model="form.name"
                                required
                            />
                            <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="total_amount">Monto Total</Label>
                                <Input
                                    id="total_amount"
                                    type="number"
                                    step="0.01"
                                    v-model="form.total_amount"
                                    required
                                />
                                <p v-if="form.errors.total_amount" class="text-sm text-red-500">{{ form.errors.total_amount }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="remaining_amount">Monto Restante</Label>
                                <Input
                                    id="remaining_amount"
                                    type="number"
                                    step="0.01"
                                    v-model="form.remaining_amount"
                                    required
                                />
                                <p v-if="form.errors.remaining_amount" class="text-sm text-red-500">{{ form.errors.remaining_amount }}</p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="monthly_payment">Pago Mensual (Opcional)</Label>
                                <Input
                                    id="monthly_payment"
                                    type="number"
                                    step="0.01"
                                    :model-value="form.monthly_payment"
                                    @update:model-value="(v) => form.monthly_payment = v === '' ? null : Number(v)"
                                />
                                <p v-if="form.errors.monthly_payment" class="text-sm text-red-500">{{ form.errors.monthly_payment }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="due_day">Día de Pago (1-31)</Label>
                                <Input
                                    id="due_day"
                                    type="number"
                                    min="1"
                                    max="31"
                                    :model-value="form.due_day"
                                    @update:model-value="(v) => form.due_day = v === '' ? null : Number(v)"
                                />
                                <p v-if="form.errors.due_day" class="text-sm text-red-500">{{ form.errors.due_day }}</p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="start_date">Fecha de Inicio</Label>
                                <Input
                                    id="start_date"
                                    type="date"
                                    v-model="form.start_date"
                                    required
                                />
                                <p v-if="form.errors.start_date" class="text-sm text-red-500">{{ form.errors.start_date }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="end_date">Fecha Estimada de Fin</Label>
                                <Input
                                    id="end_date"
                                    type="date"
                                    :model-value="form.end_date"
                                    @update:model-value="(v) => form.end_date = v === '' ? null : String(v)"
                                />
                                <p v-if="form.errors.end_date" class="text-sm text-red-500">{{ form.errors.end_date }}</p>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                             <Label for="total_installments">Total de Mensualidades (Opcional)</Label>
                             <Input
                                 id="total_installments"
                                 type="number"
                                 :model-value="form.total_installments"
                                 @update:model-value="(v) => form.total_installments = v === '' ? null : Number(v)"
                                 placeholder="Ej. 12, 24, 36"
                             />
                             <p v-if="form.errors.total_installments" class="text-sm text-red-500">{{ form.errors.total_installments }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="notes">Notas</Label>
                            <Textarea
                                id="notes"
                                :model-value="form.notes"
                                @update:model-value="(v) => form.notes = v === '' ? null : String(v)"
                            />
                            <p v-if="form.errors.notes" class="text-sm text-red-500">{{ form.errors.notes }}</p>
                        </div>

                        <div class="flex justify-end gap-4">
                            <Link :href="route('debts.index')">
                                <Button type="button" variant="outline">Cancelar</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Actualizar' : 'Actualizar Deuda' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
