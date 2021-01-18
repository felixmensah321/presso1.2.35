FROM node:lts-alpine AS builder
WORKDIR /home/node
USER node

COPY --chown=node . ./
RUN npm install
RUN npm run build

FROM nginx:stable-alpine
WORKDIR /usr/share/nginx/html
USER root

COPY --from=builder /home/node/dist .
