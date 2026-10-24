<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { ideasApi, authApi } from '../api/ideas'
import { auth } from '../api/http'
import { STATUSES, DIFFICULTIES } from '../constants'
import IdeaCard from '../components/IdeaCard.vue'
import IdeaForm from '../components/IdeaForm.vue'

const router = useRouter()

const user = ref(null)
const ideas = ref([])
const stats = ref(null)
const loading = ref(true)
const loadError = ref('')
const filters = ref({ status: '', difficulty: '', q: '' })

const dialog = ref(null)
const editing = ref(null)
const saving = ref(false)
const formErrors = ref({})

const rouletteDialog = ref(null)
const rouletteIdea = ref(null)
const rouletteMessage = ref('')
const spinning = ref(false)

const toast = ref('')
let toastTimer
function notify(message) {
  toast.value = message
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => (toast.value = ''), 2600)
}

async function load() {
  loading.value = true
  loadError.value = ''
  try {
    const params = Object.fromEntries(Object.entries(filters.value).filter(([, v]) => v))
    const [list, s] = await Promise.all([ideasApi.list(params), ideasApi.stats()])
    ideas.value = list
    stats.value = s
  } catch {
    loadError.value = 'Gagal memuat ide dari server.'
  } finally {
    loading.value = false
  }
}

let searchTimer
watch(
  () => ({ ...filters.value }),
  () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(load, 250)
  },
)

onMounted(async () => {
  load()
  user.value = await authApi.me().catch(() => null)
})

function openCreate() {
  editing.value = null
  formErrors.value = {}
  dialog.value.showModal()
}

function openEdit(idea) {
  editing.value = idea
  formErrors.value = {}
  dialog.value.showModal()
}

function closeForm() {
  dialog.value.close()
}

async function save(payload) {
  saving.value = true
  formErrors.value = {}
  try {
    if (editing.value) {
      await ideasApi.update(editing.value.id, payload)
      notify('Ide diperbarui ✏️')
    } else {
      await ideasApi.create(payload)
      notify('Ide masuk vault 💡')
    }
    closeForm()
    await load()
  } catch (e) {
    if (e.response?.status === 422) formErrors.value = e.response.data.errors
    else notify('Gagal menyimpan ide.')
  } finally {
    saving.value = false
  }
}

async function remove(idea) {
  if (!confirm(`Hapus "${idea.title}" dari vault?`)) return
  await ideasApi.remove(idea.id)
  notify('Ide dihapus 🗑️')
  await load()
}

async function togglePin(idea) {
  await ideasApi.update(idea.id, { is_pinned: !idea.is_pinned })
  await load()
}

async function spin() {
  rouletteIdea.value = null
  rouletteMessage.value = ''
  spinning.value = true
  rouletteDialog.value.showModal()
  try {
    const [idea] = await Promise.all([ideasApi.roulette(), new Promise((r) => setTimeout(r, 900))])
    rouletteIdea.value = idea
  } catch (e) {
    rouletteMessage.value = e.response?.data?.message ?? 'Roda macet. Coba lagi.'
  } finally {
    spinning.value = false
  }
}

async function startBuilding() {
  await ideasApi.update(rouletteIdea.value.id, { status: 'building' })
  rouletteDialog.value.close()
  notify('Gas! Status jadi Building 🔨')
  await load()
}

async function logout() {
  await authApi.logout().catch(() => {})
  auth.clear()
  router.push({ name: 'login' })
}

const statusEntries = computed(() => Object.entries(STATUSES))
</script>

<template>
  <div class="vault">
    <header class="topbar">
      <div class="brand">
        <span class="brand-mark">IV</span>
        <div>
          <strong>IdeaVault</strong>
          <small v-if="user">vault milik {{ user.name }}</small>
        </div>
      </div>
      <div class="topbar-actions">
        <button class="btn btn-roulette" @click="spin">🎲 Gak tau mau ngerjain apa?</button>
        <button class="btn btn-ghost" @click="logout">Logout</button>
      </div>
    </header>

    <section v-if="stats" class="stats" aria-label="Statistik ide">
      <div class="stat-total">
        <span class="num">{{ stats.total }}</span>
        <span>ide tersimpan</span>
      </div>
      <button
        v-for="[key, s] in statusEntries"
        :key="key"
        class="stat"
        :class="[`status-${key}`, { active: filters.status === key }]"
        @click="filters.status = filters.status === key ? '' : key"
      >
        <span class="num">{{ stats.by_status[key] }}</span>
        <span>{{ s.emoji }} {{ s.label }}</span>
      </button>
      <div class="stat-rate">
        <span class="num">{{ stats.shipping_rate }}%</span>
        <span>shipping rate</span>
      </div>
    </section>

    <section class="toolbar">
      <input v-model="filters.q" type="search" placeholder="Cari judul, tagline, deskripsi…" />
      <select v-model="filters.difficulty" aria-label="Filter kesulitan">
        <option value="">Semua level</option>
        <option v-for="(d, key) in DIFFICULTIES" :key="key" :value="key">{{ d.label }}</option>
      </select>
      <button class="btn btn-primary" @click="openCreate">+ Tangkap ide</button>
    </section>

    <p v-if="loadError" class="form-error">{{ loadError }}</p>
    <p v-else-if="loading && !ideas.length" class="empty">Membuka vault…</p>
    <div v-else-if="!ideas.length" class="empty">
      <p>Vault masih kosong (atau filternya terlalu ketat).</p>
      <button class="btn btn-primary" @click="openCreate">Tangkap ide pertama</button>
    </div>

    <TransitionGroup v-else tag="section" name="list" class="grid">
      <IdeaCard
        v-for="idea in ideas"
        :key="idea.id"
        :idea="idea"
        @edit="openEdit"
        @delete="remove"
        @toggle-pin="togglePin"
      />
    </TransitionGroup>

    <dialog ref="dialog" class="modal" @close="editing = null">
      <IdeaForm :idea="editing" :saving="saving" :errors="formErrors" @save="save" @cancel="closeForm" />
    </dialog>

    <dialog ref="rouletteDialog" class="modal roulette">
      <div v-if="spinning" class="roulette-spin">
        <span class="die">🎲</span>
        <p>Mengocok ide berdasarkan hype…</p>
      </div>
      <div v-else-if="rouletteIdea" class="roulette-result">
        <p class="eyebrow">Ide kamu hari ini</p>
        <h2>{{ rouletteIdea.title }}</h2>
        <p v-if="rouletteIdea.tagline" class="tagline">“{{ rouletteIdea.tagline }}”</p>
        <p class="muted">Hype {{ '🔥'.repeat(rouletteIdea.hype_level) }} · {{ rouletteIdea.difficulty }}</p>
        <div class="actions">
          <button class="btn btn-ghost" @click="spin">Kocok lagi</button>
          <button class="btn btn-primary" @click="startBuilding">Gas, kerjain! 🔨</button>
        </div>
      </div>
      <div v-else class="roulette-result">
        <p>{{ rouletteMessage }}</p>
        <div class="actions">
          <button class="btn btn-ghost" @click="rouletteDialog.close()">Tutup</button>
        </div>
      </div>
    </dialog>

    <Transition name="toast">
      <div v-if="toast" class="toast" role="status">{{ toast }}</div>
    </Transition>
  </div>
</template>
