<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import InputError from '@/Components/Default/InputError.vue';
import InputLabel from '@/Components/Default/InputLabel.vue';
import PrimaryButton from '@/Components/Default/PrimaryButton.vue';
import TextInput from '@/Components/Default/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Підтвердженян пароля" />

        <div class="mb-4 text-sm text-gray-600">
            Ця зона сайту захищена. Підтвердіть пароль, перш ніж продовжити.
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="password" value="Пароль" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 flex justify-end">
                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Підтвердити
                </PrimaryButton>
            </div>
        </form>
    </AuthLayout>
</template>
