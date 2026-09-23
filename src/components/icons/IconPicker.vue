<template>
  <font-awesome-icon :icon="icon" :class="['inline', colorClass]" />
</template>

<script setup>
  import { computed } from 'vue'
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
  import {
    faBook,
    faHeartPulse,
    faBullseye,
    faComment,
    faDove,
    faCircleInfo,
    faTriangleExclamation,
    faCheck,
    faXmark,
    faPlus,
    faTrash,
    faStar as faStarSolid,
    faClock,
    faHourglass,
    faMoneyBillWave,
    faUser,
    faMars,
    faVenus,
    faTrophy,
    faEye,
    faChartLine,
    faLink,
    faPalette,
    faHeart,
  } from '@fortawesome/free-solid-svg-icons'
  import { faStar as faStarRegular } from '@fortawesome/free-regular-svg-icons'

  const iconMap = {
    book: faBook,
    health: faHeartPulse,
    strategy: faBullseye,
    chat: faComment,
    pigeon: faDove,
    info: faCircleInfo,
    warning: faTriangleExclamation,
    check: faCheck,
    x: faXmark,
    plus: faPlus,
    delete: faTrash,
    star: faStarSolid,
    'star-outline': faStarRegular,
    clock: faClock,
    hourglass: faHourglass,
    money: faMoneyBillWave,
    user: faUser,
    male: faMars,
    female: faVenus,
    trophy: faTrophy,
    eye: faEye,
    chart: faChartLine,
    link: faLink,
    palette: faPalette,
    heart: faHeart,
  }

  const props = defineProps({
    name: {
      type: String,
      required: true,
      validator: (value) =>
        [
          'book',
          'health',
          'strategy',
          'chat',
          'pigeon',
          'info',
          'warning',
          'check',
          'x',
          'plus',
          'delete',
          'star',
          'star-outline',
          'clock',
          'hourglass',
          'money',
          'user',
          'male',
          'female',
          'trophy',
          'eye',
          'chart',
          'link',
          'palette',
          'heart',
        ].includes(value),
    },
    colorClass: {
      type: String,
      default: 'text-gray-600',
    },
  })

  const icon = computed(() => iconMap[props.name])
</script>
