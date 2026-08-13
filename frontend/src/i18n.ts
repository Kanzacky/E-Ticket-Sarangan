import { createI18n } from 'vue-i18n'

import en from '@/locales/en.json'
import id from '@/locales/id.json'

const defaultLocale = (import.meta.env.VITE_DEFAULT_LOCALE as string) || 'id'

export const i18n = createI18n({
  legacy: false,
  locale: defaultLocale,
  fallbackLocale: 'en',
  messages: {
    id,
    en,
  },
  datetimeFormats: {
    'id-ID': {
      long: {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      },
      time: {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
      },
      longTime: {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
      },
    },
    'en-US': {
      long: {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      },
      time: {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
      },
      longTime: {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
      },
    },
  },
})

export default i18n
