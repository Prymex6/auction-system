import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import '@/assets/styles.css'
import { cleanupOldData, getUsageFormatted } from '@/utils/localStorage'

// <font-awesome-icon> nic nie wyrenderuje.
import { library } from '@fortawesome/fontawesome-svg-core'
import {
  faArrowDown,
  faArrowRight,
  faArrowRightFromBracket,
  faArrowRightToBracket,
  faArrowsRotate,
  faArrowTrendDown,
  faArrowTrendUp,
  faBan,
  faBars,
  faBell,
  faBolt,
  faCalendar,
  faCertificate,
  faChartBar,
  faChartLine,
  faCheck,
  faCircle,
  faChevronDown,
  faChevronLeft,
  faChevronRight,
  faCircleCheck,
  faCircleExclamation,
  faCircleInfo,
  faCirclePlus,
  faClipboard,
  faClock,
  faCloud,
  faComment,
  faCommentDots,
  faCookieBite,
  faCrown,
  faDesktop,
  faDownload,
  faEnvelope,
  faEye,
  faFileLines,
  faGavel,
  faGear,
  faGift,
  faHeart,
  faHourglass,
  faHouse,
  faImage,
  faInbox,
  faKey,
  faLink,
  faListUl,
  faLocationDot,
  faLock,
  faMagnifyingGlass,
  faMobileScreenButton,
  faPaperPlane,
  faPenToSquare,
  faPhone,
  faPlus,
  faSackDollar,
  faShareNodes,
  faShieldHalved,
  faSliders,
  faSpinner,
  faStar,
  faStarHalfStroke,
  faTag,
  faTrash,
  faTriangleExclamation,
  faTrophy,
  faUser,
  faUsers,
  faWandMagicSparkles,
  faXmark,
} from '@fortawesome/free-solid-svg-icons'
import { faStar as farStar, faHeart as farHeart } from '@fortawesome/free-regular-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
library.add(
  faArrowDown,
  faArrowRight,
  faArrowRightFromBracket,
  faArrowRightToBracket,
  faArrowsRotate,
  faArrowTrendDown,
  faArrowTrendUp,
  faBan,
  faBars,
  faBell,
  faBolt,
  faCalendar,
  faCertificate,
  faChartBar,
  faChartLine,
  faCheck,
  faCircle,
  faChevronDown,
  faChevronLeft,
  faChevronRight,
  faCircleCheck,
  faCircleExclamation,
  faCircleInfo,
  faCirclePlus,
  faClipboard,
  faClock,
  faCloud,
  faComment,
  faCommentDots,
  faCookieBite,
  faCrown,
  faDesktop,
  faDownload,
  faEnvelope,
  faEye,
  faFileLines,
  faGavel,
  faGear,
  faGift,
  faHeart,
  faHourglass,
  faHouse,
  faImage,
  faInbox,
  faKey,
  faLink,
  faListUl,
  faLocationDot,
  faLock,
  faMagnifyingGlass,
  faMobileScreenButton,
  faPaperPlane,
  faPenToSquare,
  faPhone,
  faPlus,
  faSackDollar,
  faShareNodes,
  faShieldHalved,
  faSliders,
  faSpinner,
  faStar,
  faStarHalfStroke,
  faTag,
  faTrash,
  faTriangleExclamation,
  faTrophy,
  faUser,
  faUsers,
  faWandMagicSparkles,
  faXmark,
  farStar,
  farHeart
)

// Initialize Pusher for real-time updates
import Pusher from 'pusher-js'

// Configure Pusher
window.Pusher = Pusher

Pusher.logToConsole = import.meta.env.DEV // Enable debug in development

// Log localStorage usage in development
if (import.meta.env.DEV) {
  console.log(`📦 localStorage usage: ${getUsageFormatted()}`)
}

const app = createApp(App)
app.component('FontAwesomeIcon', FontAwesomeIcon)

// Global error handler for localStorage quota issues
window.addEventListener('error', (event) => {
  if (event.error?.name === 'QuotaExceededError') {
    console.error('localStorage quota exceeded, running cleanup...')
    cleanupOldData()
    event.preventDefault()
  }
})

// Handle unhandled promise rejections
window.addEventListener('unhandledrejection', (event) => {
  if (event.reason?.name === 'QuotaExceededError') {
    console.error('localStorage quota exceeded in promise, running cleanup...')
    cleanupOldData()
    event.preventDefault()
  }
})

app.use(createPinia())
app.use(router)

app.mount('#app')

if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch((err) => {
      console.warn('Nie udało się zarejestrować service workera:', err)
    })
  })
}
