<template>
  <router-view></router-view>
</template>

<style>
/* Global transitions if needed */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
