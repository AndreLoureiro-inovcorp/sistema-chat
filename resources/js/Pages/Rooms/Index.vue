<script setup>
import { ref } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
    rooms: Array,
})

const selectedRoom = ref(null)
const messages = ref([])
const newMessage = ref('')

const selectRoom = async (room) => {
    selectedRoom.value = room
    await fetchMessages()
}

const fetchMessages = async () => {
    if (!selectedRoom.value) return

    const response = await axios.get(`/rooms/${selectedRoom.value.id}/messages`)
    messages.value = response.data
}

const sendMessage = async () => {
    if (!newMessage.value.trim() || !selectedRoom.value) return

    await axios.post(`/rooms/${selectedRoom.value.id}/messages`, {
        content: newMessage.value,
    })

    newMessage.value = ''
    fetchMessages()
}

const handleKeypress = (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault()
        sendMessage()
    }
}

const deleteRoom = (roomId) => {
    if (confirm('Tens a certeza que queres apagar esta sala?')) {
        router.delete(`/rooms/${roomId}`)
    }
}
</script>

<template>

    <Head title="Salas" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="card bg-base-100 shadow">
                    <div class="flex h-[600px]">

                        <div class="w-1/3 border-r p-3">
                            <div class="flex justify-between items-center p-4 border-b">
                                <h2 class="text-lg font-semibold">Salas</h2>

                                <Link v-if="$page.props.auth.user.role === 'admin'" href="/rooms/create"
                                    class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                    Nova Sala
                                </Link>
                            </div>

                            <ul class="menu bg-base-100 rounded-xl p-2 gap-1">
                                <li v-for="room in rooms" :key="room.id">
                                    <a @click="selectRoom(room)" :class="{ 'active': selectedRoom?.id === room.id }">
                                        <div class="flex items-center gap-3 w-full">
                                            <div v-if="room.avatar" class="avatar">
                                                <div class="w-10 rounded-full">
                                                    <img :src="`/storage/${room.avatar}`" :alt="room.name"
                                                        class="object-cover" />
                                                </div>
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <div class="font-semibold truncate">{{ room.name }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ room.users.length }} membros
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="flex-1 flex flex-col">
                            <div v-if="selectedRoom" class="flex-1 flex flex-col">

                                <div class="p-4 border-b flex justify-between items-center">

                                    <div class="flex items-center gap-3">
                                        <div v-if="selectedRoom.avatar" class="avatar">
                                            <div class="w-12 rounded-full">
                                                <img :src="`/storage/${selectedRoom.avatar}`" :alt="selectedRoom.name"
                                                    class="object-cover" />
                                            </div>
                                        </div>

                                        <div>
                                            <h3 class="font-semibold">{{ selectedRoom.name }}</h3>
                                            <p class="text-xs text-gray-500">
                                                {{ selectedRoom.users.length }} membros
                                            </p>
                                        </div>
                                    </div>

                                    <div v-if="$page.props.auth.user.role === 'admin'" class="flex gap-2">
                                        <Link :href="`/rooms/${selectedRoom.id}/edit`"
                                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition flex items-center gap-1">

                                            <span>Editar Sala</span>
                                        </Link>
                                        <button @click="deleteRoom(selectedRoom.id)"
                                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition flex items-center gap-1">
                                            <span>Eliminar Sala</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex-1 p-4 overflow-y-auto bg-base-200 max-h-[500px]">
                                    <div v-if="messages.length === 0" class="text-center text-gray-500 py-8">
                                        Esta sala ainda não tem mensagens.
                                    </div>

                                    <div v-else class="space-y-2">
                                        <div v-for="message in messages" :key="message.id" class="chat"
                                            :class="message.user_id === $page.props.auth.user.id ? 'chat-end' : 'chat-start'">

                                            <div class="chat-image avatar">
                                                <div v-if="message.user.avatar" class="w-10 rounded-full">
                                                    <img :src="`/storage/${message.user.avatar}`"
                                                        :alt="message.user.name" class="object-cover" />
                                                </div>
                                                <div v-else class="avatar placeholder">
                                                    <div class="bg-neutral text-neutral-content rounded-full w-10">
                                                        <span class="text-xs">{{ message.user.name.charAt(0) }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="chat-header text-xs opacity-50 mb-1">
                                                {{ message.user.name }}
                                            </div>

                                            <div class="chat-bubble" :class="message.user_id === $page.props.auth.user.id
                                                ? 'chat-bubble-primary'
                                                : 'chat-bubble-secondary'">
                                                <p class="text-sm">{{ message.content }}</p>
                                                <span class="text-xs opacity-70 block mt-1">
                                                    {{
                                                        new Date(message.created_at).toLocaleTimeString('pt-PT', {
                                                            hour: '2-digit',
                                                            minute: '2-digit'
                                                        })
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

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
                            </div>

                            <div v-else class="flex-1 flex items-center justify-center text-gray-500">
                                Seleciona uma sala para começar a conversar
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
