<template>
    <div>
        <nav class="uk-navbar-container" uk-navbar>
            <div class="uk-navbar-left">
                <router-link :to="{ name: 'home' }">ImageGallery</router-link>
                <ul class="uk-navbar-nav">
                    <li>
                        <router-link :to="{ name: 'home' }">Home</router-link>
                    </li>
                    <li>
                        <router-link :to="{ name: 'contact' }">Contact</router-link>
                    </li>
                </ul>
            </div>
            <div class="uk-navbar-right">
                <ul class="uk-navbar-nav">
                    <li v-if="$auth.check()">
                        <router-link :to="{ name: 'account.show' }">{{ $auth.user().name }}</router-link>
                        <div class="uk-navbar-dropdown">
                            <ul class="uk-nav uk-navbar-dropdown-nav">
                                <li>
                                    <router-link :to="{ name: 'account.images' }">Images</router-link>
                                </li>
                                <li>
                                    <a href="" @click.prevent="$auth.logout()">Logout</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li v-if="!$auth.check()">
                        <router-link :to="{ name: 'register' }">Register</router-link>
                    </li>
                    <li v-if="!$auth.check()">
                        <router-link :to="{ name: 'login' }">Login</router-link>
                    </li>
                </ul>

            </div>
        </nav>
        <div uk-height-viewport="offset-top: true" data-src="/assets/img/background.jpg" uk-img class="uk-background-cover">
            <div v-if="$auth.ready()">
                <router-view></router-view>
            </div>
            <div class="uk-position-center" v-else>
                <h1>Please wait whilst the site is loading.</h1>
            </div>
            <!--            <router-view></router-view>-->
        </div>
    </div>
</template>
<script>
    export default {}
</script>
