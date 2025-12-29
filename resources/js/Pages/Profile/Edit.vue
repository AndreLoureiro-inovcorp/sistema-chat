<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const avatarForm = useForm({
    avatar: null,
});

const avatarPreview = ref(null);

const handleAvatarChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        avatarForm.avatar = file;

        const reader = new FileReader();
        reader.onload = (e) => {
            avatarPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const updateAvatar = () => {
    avatarForm.post(route('profile.avatar.update'), {
        preserveScroll: true,
        onSuccess: () => {
            avatarForm.reset();
            avatarPreview.value = null;
        },
    });
};
</script>

<template>

    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">Avatar</h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Adiciona ou atualiza a tua foto de perfil.
                        </p>
                    </header>

                    <form @submit.prevent="updateAvatar" class="mt-6 space-y-6">
                        <div class="flex items-start gap-6">
                            <div>
                                <div v-if="avatarPreview || $page.props.auth.user.avatar" class="avatar">
                                    <div class="w-24 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                                        <img :src="avatarPreview || `/storage/${$page.props.auth.user.avatar}`"
                                            alt="Avatar" class="object-cover" />
                                    </div>
                                </div>
                                <div v-else class="avatar placeholder">
                                    <div class="bg-neutral text-neutral-content rounded-full w-24">
                                        <span class="text-3xl">{{ $page.props.auth.user.name.charAt(0) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-1">
                                <input type="file" accept="image/jpeg,image/png,image/jpg,image/webp"
                                    @change="handleAvatarChange"
                                    class="file-input file-input-bordered w-full max-w-xs" />
                                <p class="mt-2 text-sm text-gray-600">
                                    JPG, PNG ou WebP. Máx 2MB.
                                </p>

                                <button type="submit" :disabled="!avatarForm.avatar || avatarForm.processing" class="px-4 py-2 text-sm font-medium text-white bg-gray-800 rounded-lg hover:bg-gray-700 transition flex items-center gap-1 mt-4">
                                    <span v-if="avatarForm.processing">A guardar...</span>
                                    <span v-else>Guardar Avatar</span>
                                </button>

                                <p v-if="avatarForm.recentlySuccessful" class="text-sm text-green-600 mt-2">
                                    Avatar atualizado!
                                </p>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Profile Information -->
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status"
                        class="max-w-xl" />
                </div>

                <!-- Update Password -->
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <!-- Delete Account -->
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
