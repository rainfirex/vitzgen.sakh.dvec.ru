<template>
    <div class="wrapper">
        <div class="ml-auto mr-auto mt-3 col-sm-11 offset-sm-1 col-md-10">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Body"
    }
</script>

<style scoped>
    .wrapper {
        padding-bottom: 150px !important;
    }
</style>
