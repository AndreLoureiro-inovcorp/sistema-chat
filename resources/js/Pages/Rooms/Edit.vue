<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    room: Object,
    availableUsers: Array,
});

const form = ref({
    name: props.room.name,
    avatar: null,
});

const submitting = ref(false);
const avatarPreview = ref(props.room.avatar ? `/storage/${props.room.avatar}` : null);

const inviteLink = computed(() => {
    if (typeof window === 'undefined' || !props.room.invite_token) {
        return '';
    }
    return `${window.location.origin}/salas/convite/${props.room.invite_token}`;
});

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

const removeAvatar = () => {
    form.value.avatar = null;
    avatarPreview.value = null;
};

const updateRoom = () => {
    submitting.value = true;

    router.post(`/rooms/${props.room.id}`, {
        _method: 'PUT',
        ...form.value,
    }, {
        forceFormData: true,
        onSuccess: () => {
        },
        onError: (errors) => {
            console.error(errors);
            submitting.value = false;
        },
        onFinish: () => {
            submitting.value = false;
        },
    });
};

const toggleMember = (userId, isMember) => {
    if (isMember) {
        router.delete(`/rooms/${props.room.id}/members/${userId}`);
    } else {
        router.post(`/rooms/${props.room.id}/members/${userId}`);
    }
};

const isMember = (userId) => {
    return props.room.users.some(u => u.id === userId);
};

const deleteRoom = () => {
    if (confirm('ATENÇÃO: Apagar sala é permanente! Tens a certeza?')) {
        router.delete(`/rooms/${props.room.id}`);
    }
};

const allUsers = ref([...props.room.users]);
props.availableUsers.forEach(user => {
    if (!allUsers.value.some(u => u.id === user.id)) {
        allUsers.value.push(user);
    }
});
</script>

<template>

    <Head :title="`Editar ${room.name}`" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <form @submit.prevent="updateRoom" class="space-y-6">

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">Nome da Sala *</span>
                                </label>
                                <input v-model="form.name" type="text" class="input input-bordered w-full" required />
                            </div>
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">Avatar</span>
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

                            <div v-if="room.invite_token && inviteLink" class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">Link de Convite: </span>
                                </label>
                                <input type="text" :value="inviteLink" readonly
                                    class="input input-bordered w-full bg-gray-50" />
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">
                                        Membros da Sala
                                    </span>
                                </label>

                                <div class="border rounded-lg p-4 max-h-96 overflow-y-auto">
                                    <div v-if="allUsers.length === 0" class="text-gray-500 text-sm">
                                        Sem utilizadores disponíveis
                                    </div>
                                    <div v-else class="space-y-3">
                                        <div v-for="user in allUsers" :key="user.id"
                                            class="flex items-center justify-between p-3 hover:bg-base-200 rounded">
                                            <div class="flex items-center gap-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="avatar">
                                                        <div v-if="user.avatar" class="w-10 rounded-full">
                                                            <img :src="`/storage/${user.avatar}`" :alt="user.name"
                                                                class="object-cover" />
                                                        </div>
                                                        <div v-else class="avatar placeholder">
                                                            <div
                                                                class="bg-neutral text-neutral-content rounded-full w-10">
                                                                <span class="text-sm">{{ user.name.charAt(0) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="font-medium">{{ user.name }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <input type="checkbox" class="toggle toggle-primary"
                                                :checked="isMember(user.id)"
                                                :disabled="user.id === $page.props.auth.user.id"
                                                @change="toggleMember(user.id, isMember(user.id))" />
                                        </div>
                                    </div>
                                </div>
                                <label class="label">
                                    <span class="label-text-alt text-gray-500">
                                        Ativa/desativa o acesso dos utilizadores a esta sala
                                    </span>
                                </label>
                            </div>

                            <div class="flex gap-3 justify-between">
                                <button type="button" @click="deleteRoom"
                                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                                    Apagar Sala
                                </button>

                                <button type="submit"
                                    class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition"
                                    :disabled="!form.name.trim() || submitting">
                                    <span v-if="submitting">A guardar...</span>
                                    <span v-else>Guardar Alterações</span>
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