<template>
    <div class="">
        <nav class="navbar navbar-expand-lg">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <router-link class="nav-link" to="/sakhalin-energo/test" active-class="active">Тестовый запрос</router-link>
                </li>
                <li class="nav-item">
                    <router-link class="nav-link" to="/sakhalin-energo/data-export-omnius" active-class="active">Выгрузка (OMNIUS)</router-link>
                </li>
                <li class="nav-item">
                    <router-link class="nav-link" to="/sakhalin-energo/data-asuse-compare" active-class="active">Сверка (ASUSE / Omnius)</router-link>
                </li>
                <li class="nav-item">
                    <router-link class="nav-link" to="/sakhalin-energo/data-vitz" active-class="active">Замена лиц. номеров</router-link>
                </li>
                <li class="nav-item">
                    <router-link class="nav-link" to="/sakhalin-energo/data-validator" active-class="active">Валидатор</router-link>
                </li>
                <li class="nav-item">
                    <router-link class="nav-link" to="/sakhalin-energo/data-printer" active-class="active">Учет принтеров</router-link>
                </li>
                <li class="nav-item">
                    <router-link class="nav-link" to="/sakhalin-energo/convert/" active-class="active">Конвертор</router-link>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
    export default {
        name: "Navigator"
    }
</script>

<style scoped>
    nav {
        background: #4d4e4e;
    }
    a {
        color: #ffffff;
    }
    .active {
        color: #0bef86;
    }
</style>
