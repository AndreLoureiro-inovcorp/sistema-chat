<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UserList from '@/Components/Chat/UserList.vue';
import ChatWindow from '@/Components/Chat/ChatWindow.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    users: Array,
});

const selectedUser = ref(null);

const selectUser = (user) => {
    selectedUser.value = user;
};
</script>

<template>
    <Head title="Chat" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="card bg-base-100 shadow">
                    <div class="flex h-[600px]">
                        <UserList
                            :users="users"
                            :selectedUser="selectedUser"
                            @selectUser="selectUser"
                        />

                        <ChatWindow :selectedUser="selectedUser" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>