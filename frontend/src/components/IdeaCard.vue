<script setup>
import { computed } from 'vue'
import { STATUSES, ORIGINS } from '../constants'

const props = defineProps({ idea: { type: Object, required: true } })
defineEmits(['edit', 'delete', 'toggle-pin'])

const status = computed(() => STATUSES[props.idea.status])
const origin = computed(() => ORIGINS[props.idea.origin])

const deadlineLabel = computed(() => {
  if (!props.idea.deadline) return null
  const today = new Date(new Date().toDateString())
  const days = Math.round((new Date(props.idea.deadline) - today) / 86400000)
  if (days < 0) return { text: `Lewat ${-days} hari`, urgent: true }
  if (days === 0) return { text: 'Hari ini!', urgent: true }
  return { text: `H-${days}`, urgent: days <= 3 }
})
</script>

<template>
  <article class="card" :class="[`status-${idea.status}`, { pinned: idea.is_pinned }]">
    <header class="card-head">
      <span class="status-chip" :title="status.hint">{{ status.emoji }} {{ status.label }}</span>
      <button
        class="pin"
        :class="{ on: idea.is_pinned }"
        :aria-label="idea.is_pinned ? 'Lepas pin' : 'Pin ide ini'"
        @click="$emit('toggle-pin', idea)"
      >
        📌
      </button>
    </header>

    <h3>{{ idea.title }}</h3>
    <p v-if="idea.tagline" class="tagline">“{{ idea.tagline }}”</p>
    <p v-if="idea.description" class="desc">{{ idea.description }}</p>

    <ul v-if="idea.tech_stack.length" class="stack">
      <li v-for="tech in idea.tech_stack" :key="tech">{{ tech }}</li>
    </ul>

    <dl class="meta">
      <div>
        <dt>Hype</dt>
        <dd class="hype" :aria-label="`${idea.hype_level} dari 5`">
          <span v-for="n in 5" :key="n" :class="{ lit: n <= idea.hype_level }">🔥</span>
        </dd>
      </div>
      <div>
        <dt>Level</dt>
        <dd><span class="diff" :class="idea.difficulty">{{ idea.difficulty }}</span></dd>
      </div>
      <div>
        <dt>Asal</dt>
        <dd>{{ origin.emoji }} {{ origin.label }}</dd>
      </div>
    </dl>

    <footer class="card-foot">
      <span v-if="idea.target_event" class="event">🏁 {{ idea.target_event }}</span>
      <span v-if="deadlineLabel" class="deadline" :class="{ urgent: deadlineLabel.urgent }">
        ⏰ {{ deadlineLabel.text }}
      </span>
      <a v-if="idea.repo_url" :href="idea.repo_url" target="_blank" rel="noopener" class="repo">repo ↗</a>
      <span class="spacer" />
      <button class="btn btn-ghost" @click="$emit('edit', idea)">Ubah</button>
      <button class="btn btn-ghost danger" @click="$emit('delete', idea)">Hapus</button>
    </footer>
  </article>
</template>
