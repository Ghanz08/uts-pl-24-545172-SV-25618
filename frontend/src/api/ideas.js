import http from './http'

export const ideasApi = {
  list: (params) => http.get('/project-ideas', { params }).then((r) => r.data.data),
  get: (id) => http.get(`/project-ideas/${id}`).then((r) => r.data.data),
  create: (payload) => http.post('/project-ideas', payload).then((r) => r.data.data),
  update: (id, payload) => http.put(`/project-ideas/${id}`, payload).then((r) => r.data.data),
  remove: (id) => http.delete(`/project-ideas/${id}`),
  roulette: () => http.get('/project-ideas/roulette').then((r) => r.data.data),
  stats: () => http.get('/project-ideas/stats').then((r) => r.data.data),
}

export const authApi = {
  login: (credentials) => http.post('/login', credentials).then((r) => r.data),
  logout: () => http.post('/logout'),
  me: () => http.get('/me').then((r) => r.data.data),
}
