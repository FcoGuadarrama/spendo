<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Textarea } from '@/Components/ui/textarea'
import { ArrowLeftIcon } from 'lucide-vue-next'

const form = useForm({
    name: '',
    total_amount: '',
    remaining_amount: '',
    monthly_payment: '',
    start_date: new Date().toISOString().split('T')[0],
    end_date: '',
    due_day: '',
    total_installments: '',
    notes: '',
})

const submit = () => {
    form.post(route('debts.store'))
}

// Sync remaining amount with total initially
const onTotalChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (!form.remaining_amount) {
        form.remaining_amount = target.value;
    }
}
</script>

<template>
    <Head title="Nueva Deuda" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('debts.index')">
                    <Button variant="ghost" size="icon">
                        <ArrowLeftIcon class="h-4 w-4" />
                    </Button>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Nueva Deuda
                </h2>
            </div>
        </template>

        <div class="mx-auto max-w-2xl">
            <Card>
                <CardHeader>
                    <CardTitle>Detalles de la Deuda</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="name">Nombre / Concepto</Label>
                            <Input
                                id="name"
                                type="text"
                                v-model="form.name"
                                required
                                placeholder="Ej. Préstamo Auto"
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
                                    @input="onTotalChange"
                                    placeholder="0.00"
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
                                    placeholder="0.00"
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
                                    v-model="form.monthly_payment"
                                    placeholder="0.00"
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
                                    v-model="form.due_day"
                                    placeholder="Ej. 15"
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
                                    v-model="form.end_date"
                                />
                                <p v-if="form.errors.end_date" class="text-sm text-red-500">{{ form.errors.end_date }}</p>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                             <Label for="total_installments">Total de Mensualidades (Opcional)</Label>
                             <Input
                                 id="total_installments"
                                 type="number"
                                 v-model="form.total_installments"
                                 placeholder="Ej. 12, 24, 36"
                             />
                             <p v-if="form.errors.total_installments" class="text-sm text-red-500">{{ form.errors.total_installments }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="notes">Notas</Label>
                            <Textarea
                                id="notes"
                                v-model="form.notes"
                                placeholder="Detalles adicionales..."
                            />
                            <p v-if="form.errors.notes" class="text-sm text-red-500">{{ form.errors.notes }}</p>
                        </div>

                        <div class="flex justify-end gap-4">
                            <Link :href="route('debts.index')">
                                <Button type="button" variant="outline">Cancelar</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Guardando...' : 'Guardar Deuda' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
