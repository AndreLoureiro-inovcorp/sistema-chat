<script setup>
defineProps({
    users: Array,
    selectedUser: Object,
});

const emit = defineEmits(['selectUser']);
</script>

<template>
    <div class="w-1/3 border-r">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Conversas</h2>
        </div>

        <ul class="menu bg-base-100">
            <li v-for="user in users" :key="user.id">
                <a
                    @click="emit('selectUser', user)"
                    :class="{ 'active': selectedUser?.id === user.id }"
                >
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div class="bg-neutral text-neutral-content rounded-full w-8">
                            </div>
                        </div>
                        <span>{{ user.name }}</span>
                    </div>
                </a>
            </li>
        </ul>
    </div>
</template>