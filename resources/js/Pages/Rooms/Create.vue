<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    users: Array,
});

const form = ref({
    name: '',
    avatar: null,
    member_ids: [],
});

const submitting = ref(false);
const avatarPreview = ref(null);

const handleAvatarChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.value.avatar = file;

        const reader = new FileReader();
        reader.onload = (e) => {
            avatarPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const createRoom = () => {
    submitting.value = true;

    router.post('/rooms', form.value, {
        forceFormData: true,
        onSuccess: () => {
        },
        onError: (errors) => {
            console.error(errors);
            submitting.value = false;
        },
    });
};

const toggleMember = (userId) => {
    const index = form.value.member_ids.indexOf(userId);
    if (index > -1) {
        form.value.member_ids.splice(index, 1);
    } else {
        form.value.member_ids.push(userId);
    }
};
</script>

<template>

    <Head title="Criar Sala" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <form @submit.prevent="createRoom" class="space-y-6">

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">Nome da nova Sala *</span>
                                </label>
                                <input v-model="form.name" type="text" class="input input-bordered w-full" required />
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">Avatar (Opcional)</span>
                                </label>

                                <div class="flex items-start gap-4">
                                    <div v-if="avatarPreview" class="relative">
                                        <img :src="avatarPreview" alt="Preview"
                                            class="w-20 h-20 rounded-full object-cover border-2 border-gray-300" />
                                    </div>

                                    <div class="flex-1">
                                        <input type="file" accept="image/jpeg,image/png,image/jpg,image/webp"
                                            @change="handleAvatarChange"
                                            class="file-input file-input-bordered w-full" />
                                    </div>
                                </div>
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">
                                        Adicionar Membros ({{ form.member_ids.length }} selecionados)
                                    </span>
                                </label>
                                <div class="border rounded-lg p-4 max-h-64 overflow-y-auto">
                                    <div v-if="users.length === 0" class="text-gray-500 text-sm">
                                        Sem outros users disponíveis
                                    </div>
                                    <div v-else class="space-y-2">
                                        <label v-for="user in users" :key="user.id"
                                            class="flex items-center gap-3 p-2 hover:bg-base-200 rounded cursor-pointer">
                                            <input type="checkbox" class="checkbox"
                                                :checked="form.member_ids.includes(user.id)"
                                                @change="toggleMember(user.id)" />
                                            <div class="flex items-center gap-2">
                                                <div class="avatar">
                                                    <div v-if="user.avatar" class="w-8 rounded-full">
                                                        <img :src="`/storage/${user.avatar}`" :alt="user.name"
                                                            class="object-cover" />
                                                    </div>
                                                    <div v-else class="avatar placeholder">
                                                        <div class="bg-neutral text-neutral-content rounded-full w-8">
                                                            <span class="text-xs">{{ user.name.charAt(0) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span>{{ user.name }}</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-2 justify-end">
                                <button type="submit"
                                    class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition"
                                    :disabled="!form.name.trim() || submitting">
                                    <span v-if="submitting" class="loading loading-spinner loading-sm"></span>
                                    <span v-else>Criar Sala</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="mb-6 mt-4">
                    <Link href="/rooms" class="btn btn-sm btn-ghost">
                        ← Voltar
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>