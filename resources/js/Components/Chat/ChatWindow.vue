<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import MessageInput from './MessageInput.vue';

const props = defineProps({
    selectedUser: Object,
});

const messages = ref([]);

const fetchMessages = async () => {
    if (!props.selectedUser) return;

    const response = await axios.get(`/messages/${props.selectedUser.id}`);
    messages.value = response.data;
};

watch(() => props.selectedUser, () => {
    if (props.selectedUser) {
        fetchMessages();
    }
}, { immediate: true });
</script>

<template>
    <div class="flex-1 flex flex-col">
        <div v-if="selectedUser" class="flex-1 flex flex-col">

            <div class="p-4 border-b font-semibold">
                {{ selectedUser.name }}
            </div>

            <div class="flex-1 p-4 overflow-y-auto bg-base-200">
                <div v-if="messages.length === 0" class="alert alert-info">
                    Sem mensagens ainda. Envia a primeira!
                </div>

                <div v-else class="space-y-2">
                    <div v-for="message in messages" :key="message.id" class="chat" :class="message.user_id === $page.props.auth.user.id
                        ? 'chat-end'
                        : 'chat-start'">
                        <div class="chat-bubble" :class="message.user_id === $page.props.auth.user.id
                            ? 'chat-bubble-primary'
                            : 'chat-bubble-secondary'">
                            <p class="text-sm">{{ message.content }}</p>
                            <span class="text-xs opacity-70 block mt-1">
                                {{
                                    new Date(message.created_at)
                                        .toLocaleTimeString('pt-PT', {
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        })
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <MessageInput :selectedUser="selectedUser" @messageSent="fetchMessages" />
        </div>

        <div v-else class="flex-1 flex items-center justify-center text-gray-500">
            Seleciona um utilizador para começar a conversar
        </div>
    </div>
</template>