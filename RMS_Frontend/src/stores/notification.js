import { defineStore } from 'pinia'

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    notifications: [],
  }),

  actions: {
    add({ type = 'info', title = '', message = '', duration = 4000 }) {
      const id = Date.now() + Math.random().toString(36).substr(2, 5)
      const notification = { id, type, title, message }
      this.notifications.push(notification)

      if (duration > 0) {
        setTimeout(() => {
          this.remove(id)
        }, duration)
      }
      return id
    },

    success(message, title = 'Success') {
      return this.add({ type: 'success', title, message })
    },

    error(message, title = 'Error') {
      return this.add({ type: 'error', title, message, duration: 6000 })
    },

    info(message, title = 'Information') {
      return this.add({ type: 'info', title, message })
    },

    documentDeleted(message = 'Document deleted successfully.', title = 'Success') {
      return this.add({ type: 'success', title, message })
    },

    warning(message, title = 'Warning') {
      return this.add({ type: 'warning', title, message, duration: 5000 })
    },

    remove(id) {
      const index = this.notifications.findIndex((n) => n.id === id)
      if (index !== -1) {
        this.notifications.splice(index, 1)
      }
    },
  },
})
