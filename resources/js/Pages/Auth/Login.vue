<script setup lang="ts">
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import InputError from "@/Components/InputError.vue";
import {useForm} from "@inertiajs/vue3";

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};

</script>

<template>
    <div class="w-full lg:grid lg:min-h-[600px] lg:grid-cols-2 xl:min-h-[800px]">
        <div class="flex items-center justify-center py-12">
            <div class="mx-auto grid w-[350px] gap-6">
                <div class="grid gap-2 text-center">
                    <h1 class="text-3xl font-bold">
                        Login
                    </h1>
                    <p class="text-balance text-muted-foreground">
                        Enter your email below to login to your account
                    </p>
                </div>
                <form @submit.prevent="submit">
                    <div class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="m@example.com"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                        <div class="grid gap-2">
                            <div class="flex items-center">
                                <Label for="password">Password</Label>
                                <a
                                    href="/forgot-password"
                                    class="ml-auto inline-block text-sm underline"
                                >
                                    Forgot your password?
                                </a>
                            </div>
                            <Input
                                v-model="form.password"
                                id="password"
                                type="password"
                                required />
                            <InputError class="mt-2" :message="form.errors.password" />

                        </div>
                        <Button type="submit" class="w-full">
                            Login
                        </Button>
                    </div>
                </form>
                <div class="mt-4 text-center text-sm">
                    Don't have an account?
                    <a href="#" class="underline">
                        Sign up
                    </a>
                </div>
            </div>
        </div>
        <div class="hidden bg-muted lg:block">
            <img
                src="/placeholder.svg"
                alt="Image"
                width="1920"
                height="1080"
                class="h-full w-full object-cover dark:brightness-[0.2] dark:grayscale"
            >
        </div>
    </div>
</template>
