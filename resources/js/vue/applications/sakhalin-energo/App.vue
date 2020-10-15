<template>
    <div>
        <app-header></app-header>
        <app-body></app-body>
    </div>
</template>

<script>
    import Header from "./structures/Header";
    import Navigator from "./structures/Navigator";
    import Body from "./structures/Body";
    import Footer from "./structures/Footer";


    export default {
        name: "App",
        components: {
            appHeader:    Header,
            appBody:      Body,
        }
    }
</script>

<style>
    @font-face {
        font-family: 'Gerbera';
        src: url('http://yoursite.com/gerbera/Gerbera-Medium.woff') format('woff');
        font-weight: 400;
        font-style: normal;
    }

    * {
        /*font-family: 'Gerbera', serif;*/
        margin: 0;
        padding: 0;
    }

    html, body {
        height: 100%;
        font-size: 1.1em;
        width: 100%;
    }
    body{
        background: #6a7782;
    }
    .wrapper {
        max-width: 1300px;
        margin: 0 auto;
    }
    .bg-fon {
        background: #0e0e0ed6;
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
    }

    .view-content-code {
        height: 95%;
        max-height: 920px;
        padding: 0;
        overflow: hidden;
    }

    .main-form {
        border: solid 1px #e0e0e2;
        border-radius: 5px;
        background: #f7f7f7;
    }

    .progressbar{
        width: 100%;
        height: 10px;
    }

    ::-webkit-scrollbar{
        color: white;
        background: #babfc8;
        width: 8px;
    }
    ::-webkit-scrollbar-track {
        box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
    }
    ::-webkit-scrollbar-thumb {
        background-color: #dc2b30;
        outline: 1px solid slategrey;
    }
</style>
