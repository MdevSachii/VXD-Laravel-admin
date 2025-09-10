<template>
    <v-card>
        <v-card-title>{{ post?.ID ? 'Edit Post' : 'New Post' }}</v-card-title>
        <v-card-text>
            <v-form @submit.prevent="save">
                <v-text-field v-model="form.title" label="Title" required />
                <v-textarea v-model="form.content" label="Content" rows="8" required />
                <v-select v-model="form.status" :items="['publish','draft']" label="Status" />
                <v-text-field v-model.number="form.priority" type="number" label="Priority (Laravel only)" />
                <div class="d-flex justify-end mt-4">
                    <v-btn variant="text" @click="$emit('close')">Cancel</v-btn>
                    <v-btn color="primary" type="submit">Save</v-btn>
                </div>
            </v-form>
        </v-card-text>
    </v-card>
</template>

<script setup>
    import { reactive, watch, toRefs } from 'vue'
    import api from '../api'

    const props = defineProps({ post: Object })
    const emit = defineEmits(['close', 'saved'])

    const form = reactive({
        title: '',
        content: '',
        status: 'draft',
        priority: 0,
    })

    watch(() => props.post, (p) => {
        form.title = p?.title ?? ''
        form.content = p?.content ?? ''
        form.status = p?.status ?? 'draft'
        form.priority = p?.priority ?? 0
    }, { immediate: true })

    const save = async () => {
        try {
            if (props.post?.ID) {
                await api.put(`/posts/${props.post.ID}`, form)
            } else {
                await api.post('/posts', form)
            }
            emit('saved')
        } catch (e) {
            alert(e?.response?.data?.message || e.message)
        }
    }
</script>