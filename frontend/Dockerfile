# Stage 1: build the Vue app with Node.js.
FROM node:24.22.1-alpine AS build

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY . .

# The API base URL is configuration, not code. "/api" goes through the Nginx proxy below.
ARG VITE_API_URL=/api
ENV VITE_API_URL=${VITE_API_URL}
RUN npm run build

# Stage 2: serve only the static build with Nginx (no node_modules, no Node.js).
FROM nginx:1.30.6-alpine

# BACKEND_UPSTREAM is the Compose service name + port of the Laravel API.
ENV BACKEND_UPSTREAM=backend:8000

COPY docker/default.conf.template /etc/nginx/templates/default.conf.template
COPY --from=build /app/dist /usr/share/nginx/html

EXPOSE 80
