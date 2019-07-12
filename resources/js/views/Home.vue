<template>
    <h1 class="uk-text-center uk-position-center"><img :src="logoUrl" alt="Image Gallery Logo" id="mainPageLogoImage"></h1>
</template>
<script>
    export default {
        data() {
            return {
                logoUrl: process.env.MIX_APP_URL + '/assets/img/logo.png'
            }
        }
    }
</script>
