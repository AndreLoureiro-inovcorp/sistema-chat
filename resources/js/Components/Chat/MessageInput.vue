<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    selectedUser: Object,
});

const emit = defineEmits(['messageSent']);

const newMessage = ref('');

const sendMessage = async () => {
    if (!newMessage.value.trim() || !props.selectedUser) return;

    await axios.post('/messages', {
        content: newMessage.value,
        receiver_id: props.selectedUser.id,
    });

    newMessage.value = '';
    emit('messageSent');
};

const handleKeypress = (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
};
</script>

<template>
    <div class="p-4 border-t bg-base-100">
        <div class="flex items-center gap-3 max-w-3xl mx-auto">
            <input v-model="newMessage" type="text" placeholder="Escreve a mensagem..."
                class="flex-1 px-4 py-2 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                @keypress="handleKeypress" />

            <button
                class="px-6 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 disabled:bg-blue-300 disabled:cursor-not-allowed"
                :disabled="!newMessage.trim()" @click="sendMessage">
                Enviar
            </button>
        </div>
    </div>
</template>
