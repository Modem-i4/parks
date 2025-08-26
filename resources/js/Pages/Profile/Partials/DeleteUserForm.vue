<script setup>
import DangerButton from '@/Components/Default/DangerButton.vue';
import InputError from '@/Components/Default/InputError.vue';
import InputLabel from '@/Components/Default/InputLabel.vue';
import Modal from '@/Components/Default/Modal.vue';
import SecondaryButton from '@/Components/Default/SecondaryButton.vue';
import TextInput from '@/Components/Default/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Видалення акаунту
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Видалення акаунту анулює ваші права доступу на вебсайті.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">Видалити акаунт</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2
                    class="text-lg font-medium text-gray-900"
                >
                    Ви впевнені, що хочете видалити акаунт?
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Видалення акаунту анулює ваші права доступу на вебсайті.
                </p>

                <div class="mt-6">
                    <InputLabel
                        for="password"
                        value="Поточний пароль"
                        class="sr-only"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        placeholder="Поточний пароль"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">
                        Скасувати
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Видалити акаунт
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
