// Basic setup for Vitest + Vue Test Utils
import { config } from '@vue/test-utils'
import { library } from '@fortawesome/fontawesome-svg-core'
import { fas } from '@fortawesome/free-solid-svg-icons'
import { far } from '@fortawesome/free-regular-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

// silence warns in tests, mock global things if needed
config.global.mocks = {
  // Example: $t: (s) => s
}

// ikone Font Awesome zglaszalby "Failed to resolve component".
library.add(fas, far)
config.global.components = {
  ...config.global.components,
  'font-awesome-icon': FontAwesomeIcon,
}
