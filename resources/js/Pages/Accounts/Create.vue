<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { toast } from '@/Components/ui/toast/use-toast'

const form = ref({
    name: '',
    type: 'checking',
    balance: 0,
    credit_limit: null as number | null,
    currency: 'MXN',
    color: '#3b82f6',
    icon: 'wallet',
    include_in_total: true,
})

const errors = ref<Record<string, string>>({})

const submit = () => {
    router.post(route('accounts.store'), form.value, {
        onSuccess: () => {
            toast({ title: 'Cuenta creada', description: 'La cuenta ha sido creada exitosamente' })
        },
        onError: (pageErrors) => {
            errors.value = pageErrors as Record<string, string>
            toast({ title: 'Error al crear la cuenta', description: 'Por favor verifica los datos e intenta nuevamente', variant: 'destructive' })
        },
    })
}
</script>

<template>
    <AuthenticatedLayout>
    <Head title="Nueva Cuenta" />

    <template #header>
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Nueva Cuenta
        </h2>
    </template>

    <div class="max-w-2xl">
            <Card>
                <CardHeader>
                    <CardTitle>Crear una nueva cuenta</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <Label for="name">Nombre</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Mi Cuenta Bancaria"
                                class="mt-1"
                                :class="{ 'border-red-500': errors.name }"
                            />
                            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label for="type">Tipo de Cuenta</Label>
                                <select
                                    id="type"
                                    v-model="form.type"
                                    class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2"
                                >
                                    <option value="checking">Cuenta Corriente</option>
                                    <option value="savings">Ahorros</option>
                                    <option value="credit_card">Tarjeta de Crédito</option>
                                    <option value="cash">Efectivo</option>
                                    <option value="investment">Inversión</option>
                                </select>
                                <p v-if="errors.type" class="mt-1 text-sm text-red-600">{{ errors.type }}</p>
                            </div>

                            <div>
                                <Label for="currency">Moneda</Label>
                                <Input
                                    id="currency"
                                    v-model="form.currency"
                                    type="text"
                                    placeholder="MXN"
                                    maxlength="3"
                                    class="mt-1"
                                    :class="{ 'border-red-500': errors.currency }"
                                />
                                <p v-if="errors.currency" class="mt-1 text-sm text-red-600">{{ errors.currency }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label for="balance">Saldo Inicial</Label>
                                <Input
                                    id="balance"
                                    v-model.number="form.balance"
                                    type="number"
                                    placeholder="0.00"
                                    step="0.01"
                                    class="mt-1"
                                    :class="{ 'border-red-500': errors.balance }"
                                />
                                <p v-if="errors.balance" class="mt-1 text-sm text-red-600">{{ errors.balance }}</p>
                            </div>

                            <div>
                                <Label for="credit_limit">Límite de Crédito (Opcional)</Label>
                                <Input
                                    id="credit_limit"
                                    :model-value="form.credit_limit ?? ''"
                                    @update:model-value="(v) => form.credit_limit = v && v !== '' ? parseFloat(v as string) : null"
                                    type="number"
                                    placeholder="0.00"
                                    step="0.01"
                                    class="mt-1"
                                    :class="{ 'border-red-500': errors.credit_limit }"
                                />
                                <p v-if="errors.credit_limit" class="mt-1 text-sm text-red-600">{{ errors.credit_limit }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label for="color">Color</Label>
                                <div class="mt-1 flex gap-2">
                                    <Input
                                        id="color"
                                        v-model="form.color"
                                        type="color"
                                        class="h-10 w-16 cursor-pointer"
                                    />
                                    <Input
                                        v-model="form.color"
                                        type="text"
                                        placeholder="#3b82f6"
                                        class="flex-1"
                                    />
                                </div>
                            </div>

                            <div>
                                <Label for="icon">Icono</Label>
                                <Input
                                    id="icon"
                                    v-model="form.icon"
                                    type="text"
                                    placeholder="wallet"
                                    class="mt-1"
                                />
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                id="include_in_total"
                                v-model="form.include_in_total"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300"
                            />
                            <Label for="include_in_total" class="mb-0 cursor-pointer">
                                Incluir en el balance total
                            </Label>
                        </div>

                        <div class="flex gap-4">
                            <Button type="submit">Crear Cuenta</Button>
                            <Button type="button" variant="outline" as-child>
                                <Link :href="route('accounts.index')">Cancelar</Link>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
