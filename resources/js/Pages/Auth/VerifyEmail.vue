<script setup>
import { computed } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import PrimaryButton from '@/Components/Default/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <AuthLayout>
        <Head title="Підтвердження Email" />

        <div class="mb-4 text-sm text-gray-600">
            Дякуємо за реєстрацію! Перш ніж розпочати, чи не могли б ви підтвердити свою адресу електронної пошти, 
            натиснувши на посилання, яке ми щойно надіслали вам електронною поштою? Якщо ви не отримали електронного листа, 
            ми з радістю надішлемо вам інший.
        </div>

        <div
            class="mb-4 text-sm font-medium text-green-600"
            v-if="verificationLinkSent"
        >
            Нове посилання для підтвердження було надіслано 
            на адресу електронної пошти, яку ви вказали під час реєстрації.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Надіслати знову
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >Вийти</Link
                >
            </div>
        </form>
    </AuthLayout>
</template>
