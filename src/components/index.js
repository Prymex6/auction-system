/**
 * Components Library - Auto-import all components
 *
 * Usage in any Vue component:
 * import { Button, Badge, Input, Modal, Spinner, Alert } from '@/components'
 */

// Atomic Components
export { default as Button } from './atomic/Button.vue'
export { default as Badge } from './atomic/Badge.vue'
export { default as Input } from './atomic/Input.vue'
export { default as Select } from './atomic/Select.vue'
export { default as Checkbox } from './atomic/Checkbox.vue'
export { default as RadioGroup } from './atomic/RadioGroup.vue'
export { default as StatCard } from './atomic/StatCard.vue'

// Form Components
export { default as FormGroup } from './form/FormGroup.vue'
export { default as FormInput } from './form/FormInput.vue'
export { default as FormSelect } from './form/FormSelect.vue'
export { default as FormCheckbox } from './form/FormCheckbox.vue'
export { default as FormSection } from './form/FormSection.vue'
export { default as ImageUploadField } from './form/ImageUploadField.vue'

// Layout Components
export { default as HeroSection } from './layout/HeroSection.vue'

// Feedback Components
export { default as Spinner } from './feedback/Spinner.vue'
export { default as Alert } from './feedback/Alert.vue'
export { default as LoadingState } from './feedback/LoadingState.vue'
export { default as EmptyState } from './feedback/EmptyState.vue'
export { default as ErrorState } from './feedback/ErrorState.vue'
export { default as StatusAlert } from './feedback/StatusAlert.vue'

// Image Components
export { default as AuctionImage } from './AuctionImage.vue'
export { default as ImageUploader } from './ImageUploader.vue'
export { default as ImagePreview } from './ImagePreview.vue'
