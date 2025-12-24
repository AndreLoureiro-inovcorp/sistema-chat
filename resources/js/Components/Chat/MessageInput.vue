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
        <div class="flex gap-2">
            <input
                v-model="newMessage"
                type="text"
                placeholder="Escrever mensagem..."
                class="input input-bordered w-full"
                @keypress="handleKeypress"
            />

            <button
                class="btn btn-primary"
                :disabled="!newMessage.trim()"
                @click="sendMessage"
            >
                Enviar
            </button>
        </div>
    </div>
</template>