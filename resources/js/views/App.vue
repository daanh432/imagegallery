<template>
    <div>
        <div class="offcanvas-nav-primary" id="offCanvasMenu" uk-offcanvas="overlay: true; mode: reveal">
            <div class="uk-offcanvas-bar uk-flex uk-flex-column">
                <ul class="uk-nav uk-nav-primary uk-margin-auto-vertical uk-iconnav uk-iconnav-vertical">
                    <li>
                        <router-link :to="{ name: 'home' }"><span uk-icon="icon: home; ratio: 2"></span> Home</router-link>
                    </li>
                    <li>
                        <router-link :to="{ name: 'contact' }"><span uk-icon="icon: mail; ratio: 2"></span> Contact</router-link>
                    </li>
                    <li>
                        <router-link :to="{ name: 'users.images.index', params: {userId: $auth.user().id}}" v-if="$auth.check()"><span uk-icon="icon: image; ratio: 2"></span> Images</router-link>
                    </li>
                    <li class="uk-nav-header">Profile</li>
                    <li class="uk-nav-divider"></li>
                    <li v-if="$auth.check()">
                        <router-link :to="{ name: 'users.show', params: {userId: $auth.user().id} }"><span uk-icon="icon: user; ratio: 2"></span> {{ $auth.user().name }}</router-link>
                    </li>
                    <li v-if="$auth.check()">
                        <a @click.prevent="$auth.logout()" href=""><span uk-icon="icon: sign-out; ratio: 2"></span> Logout</a>
                    </li>
                    <li v-if="!$auth.check()">
                        <router-link :to="{ name: 'register' }">Register</router-link>
                    </li>
                    <li v-if="!$auth.check()">
                        <router-link :to="{ name: 'login' }">Login</router-link>
                    </li>
                </ul>
            </div>
        </div>
        <nav class="uk-navbar-container" uk-navbar>
            <div class="uk-navbar-left">
                <a class="uk-navbar-toggle" href="#offCanvasMenu" uk-toggle="target: #offCanvasMenu">
                    <span uk-navbar-toggle-icon></span> <span class="uk-margin-small-left">Menu</span>
                </a>
            </div>
            <div class="uk-navbar-center">
                <ul class="uk-navbar-nav">
                    <li>
                        <router-link :to="{ name: 'home' }">ImageGallery</router-link>
                    </li>
                </ul>
            </div>
        </nav>

        <div v-if="$auth.ready()">
            <router-view></router-view>
        </div>
        <div class="uk-position-center" v-else>
            <h1>Please wait whilst the site is loading.</h1>
        </div>
    </div>
</template>
<script>
    export default {}
</script>
