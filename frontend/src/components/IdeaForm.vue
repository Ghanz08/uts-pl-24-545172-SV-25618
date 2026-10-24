<script setup>
import { ref, watch } from 'vue'
import { STATUSES, ORIGINS, DIFFICULTIES } from '../constants'

const props = defineProps({
  idea: { type: Object, default: null },
  saving: { type: Boolean, default: false },
  errors: { type: Object, default: () => ({}) },
})
const emit = defineEmits(['save', 'cancel'])

const blank = () => ({
  title: '',
  tagline: '',
  description: '',
  tech_stack_text: '',
  difficulty: 'medium',
  status: 'spark',
  origin: 'shower',
  hype_level: 3,
  target_event: '',
  deadline: '',
  repo_url: '',
  is_pinned: false,
})

const form = ref(blank())

watch(
  () => props.idea,
  (idea) => {
    form.value = idea ? { ...blank(), ...idea, tech_stack_text: idea.tech_stack.join(', ') } : blank()
  },
  { immediate: true },
)

function submit() {
  const { tech_stack_text, ...rest } = form.value
  const payload = {
    title: rest.title,
    tagline: rest.tagline || null,
    description: rest.description || null,
    tech_stack: tech_stack_text.split(',').map((t) => t.trim()).filter(Boolean),
    difficulty: rest.difficulty,
    status: rest.status,
    origin: rest.origin,
    hype_level: Number(rest.hype_level),
    target_event: rest.target_event || null,
    deadline: rest.deadline || null,
    repo_url: rest.repo_url || null,
    is_pinned: rest.is_pinned,
  }
  emit('save', payload)
}

const err = (field) => props.errors[field]?.[0]
</script>

<template>
  <form class="idea-form" @submit.prevent="submit">
    <h2>{{ idea ? 'Ubah ide' : 'Tangkap ide baru' }}</h2>

    <label class="full">
      Judul ide *
      <input v-model="form.title" maxlength="120" placeholder="Misal: KosMate" />
      <small v-if="err('title')" class="field-error">{{ err('title') }}</small>
    </label>

    <label class="full">
      Tagline
      <input v-model="form.tagline" maxlength="160" placeholder="Pitch satu kalimat" />
      <small v-if="err('tagline')" class="field-error">{{ err('tagline') }}</small>
    </label>

    <label class="full">
      Deskripsi
      <textarea v-model="form.description" rows="3" placeholder="Fiturnya apa aja?" />
    </label>

    <label class="full">
      Tech stack <span class="muted">(pisahkan dengan koma)</span>
      <input v-model="form.tech_stack_text" placeholder="Laravel, Vue, FrankenPHP" />
      <small v-if="err('tech_stack')" class="field-error">{{ err('tech_stack') }}</small>
    </label>

    <fieldset class="full origin-picker">
      <legend>Idenya muncul di mana?</legend>
      <label v-for="(o, key) in ORIGINS" :key="key" :class="{ active: form.origin === key }">
        <input v-model="form.origin" type="radio" :value="key" />
        <span>{{ o.emoji }}</span>
        {{ o.label }}
      </label>
    </fieldset>

    <label>
      Status
      <select v-model="form.status">
        <option v-for="(s, key) in STATUSES" :key="key" :value="key">{{ s.emoji }} {{ s.label }}</option>
      </select>
    </label>

    <label>
      Kesulitan
      <select v-model="form.difficulty">
        <option v-for="(d, key) in DIFFICULTIES" :key="key" :value="key">{{ d.label }}</option>
      </select>
      <small v-if="err('difficulty')" class="field-error">{{ err('difficulty') }}</small>
    </label>

    <label class="full">
      Hype level: <strong>{{ '🔥'.repeat(form.hype_level) }}</strong>
      <input v-model.number="form.hype_level" type="range" min="1" max="5" />
    </label>

    <label>
      Target event
      <input v-model="form.target_event" placeholder="Gemastik 2026" />
    </label>

    <label>
      Deadline
      <input v-model="form.deadline" type="date" />
      <small v-if="err('deadline')" class="field-error">{{ err('deadline') }}</small>
    </label>

    <label class="full">
      Link repo
      <input v-model="form.repo_url" type="url" placeholder="https://github.com/…" />
      <small v-if="err('repo_url')" class="field-error">{{ err('repo_url') }}</small>
    </label>

    <label class="full checkbox">
      <input v-model="form.is_pinned" type="checkbox" />
      📌 Pin di paling atas
    </label>

    <div class="full actions">
      <button type="button" class="btn btn-ghost" @click="emit('cancel')">Batal</button>
      <button class="btn btn-primary" :disabled="saving">{{ saving ? 'Menyimpan…' : 'Simpan ide' }}</button>
    </div>
  </form>
</template>
