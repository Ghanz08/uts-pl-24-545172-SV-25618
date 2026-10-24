<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { authApi } from '../api/ideas'
import { auth } from '../api/http'

const router = useRouter()
const form = ref({ email: '', password: '' })
const error = ref('')
const loading = ref(false)

async function submit() {
  error.value = ''
  loading.value = true
  try {
    const { token } = await authApi.login(form.value)
    auth.setToken(token)
    router.push({ name: 'vault' })
  } catch (e) {
    const errors = e.response?.data?.errors
    error.value = errors ? Object.values(errors).flat()[0] : 'Server tidak bisa dihubungi.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="login">
    <section class="login-hero">
      <p class="eyebrow">IdeaVault</p>
      <h1>Ide bagus jarang datang pas lagi di depan laptop.</h1>
      <p class="lede">
        Simpan ide yang muncul pas mandi, nongkrong, atau pas dosen lagi jelasin slide 47. Nanti waktu ada
        lomba atau tugas, tinggal buka vault-nya.
      </p>
      <ul class="origin-strip" aria-hidden="true">
        <li>🚿</li>
        <li>☕</li>
        <li>🎓</li>
        <li>🌙</li>
        <li>📱</li>
      </ul>
    </section>

    <form class="login-card" @submit.prevent="submit">
      <h2>Buka vault</h2>
      <label>
        Email
        <input v-model="form.email" type="email" autocomplete="username" required />
      </label>
      <label>
        Password
        <input v-model="form.password" type="password" autocomplete="current-password" required />
      </label>
      <p v-if="error" class="form-error" role="alert">{{ error }}</p>
      <button class="btn btn-primary" :disabled="loading">
        {{ loading ? 'Membuka…' : 'Masuk' }}
      </button>
    </form>
  </main>
</template>
