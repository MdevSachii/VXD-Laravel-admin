<template>
    <div class="pa-6">
        <div class="d-flex mb-4 gap-3">
            <v-text-field v-model="search" label="Search title" hide-details density="compact" />
            <v-select v-model="sort" :items="sortItems" label="Sort by" hide-details density="compact"
                style="max-width: 220px" />
            <v-spacer />
            <v-btn color="primary" @click="openCreate">New Post</v-btn>
        </div>

        <v-data-table :headers="headers" :items="filtered" :items-per-page="10" class="elevation-1">
            <template #item.actions="{ item }">
                <v-btn size="small" variant="text" @click="openEdit(item)">Edit</v-btn>
                <v-btn size="small" variant="text" color="error" @click="remove(item)">Delete</v-btn>
            </template>
            <template #item.priority="{ item }">
                <v-text-field v-model.number="item.priority" type="number" density="compact" hide-details
                    style="max-width: 90px" @change="savePriority(item)" />
            </template>
        </v-data-table>

        <v-dialog v-model="dialog" max-width="720">
            <PostForm :post="editing" @close="dialog=false" @saved="onSaved" />
        </v-dialog>
    </div>
</template>

<script setup>
    import { ref, computed, onMounted } from 'vue'
    import api from '../api'
    import PostForm from '../components/PostForm.vue'

    const posts = ref([])
    const search = ref('')
    const sort = ref('priority')
    const sortItems = ['priority', 'date']
    const dialog = ref(false)
    const editing = ref(null)

    const headers = [
        { title: 'Title', value: 'title' },
        { title: 'Status', value: 'status' },
        { title: 'Priority', value: 'priority' },
        { title: 'Actions', value: 'actions', sortable: false },
    ]

    const fetchPosts = async () => {
        const { data } = await api.get('/posts', { params: { sort: sort.value } })
        posts.value = data.posts
    }

    const filtered = computed(() => {
        const q = search.value.toLowerCase()
        return posts.value.filter(p => (p.title || '').toLowerCase().includes(q))
    })

    const openCreate = () => { editing.value = null; dialog.value = true }
    const openEdit = (p) => { editing.value = { ...p }; dialog.value = true }

    const onSaved = async () => { dialog.value = false; await fetchPosts() }

    const remove = async (p) => {
        if (confirm('Delete this post?')) {
            try {
                await api.delete(`/posts/${p.ID}`)
                await fetchPosts()
            } catch (e) {
                alert(e?.response?.data?.message || e.message)
            }
        }
    }

    const savePriority = async (p) => {
        try {
            await api.post(`/posts/${p.ID}/priority`, { priority: p.priority ?? 0 })
        } catch (e) {
            alert(e?.response?.data?.message || e.message)
        }
    }
    onMounted(fetchPosts)
</script>