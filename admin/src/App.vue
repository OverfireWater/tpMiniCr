<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script>
export default {
  name: 'App',
  mounted() {
    document.documentElement.style.setProperty('--prev-bg-menuBar', '#015aff')
  }
}
</script>
