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
                <ul class="uk-navbar-nav" v-if="$auth.check()">
                    <li>
                        <router-link :to="{ name: 'users.images.index', params: {userId: $auth.user().id}}">Images</router-link>
                    </li>
                    <li>
                        <router-link :to="{ name: 'users.show', params: {userId: $auth.user().id} }">{{ $auth.user().name }}</router-link>
                        <div class="uk-navbar-dropdown">
                            <ul class="uk-nav uk-navbar-dropdown-nav">
                                <li>
                                    <a href="" @click.prevent="$auth.logout()">Logout</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>

                <ul class="uk-navbar-nav" v-if="!$auth.check()">
                    <li>
                        <router-link :to="{ name: 'register' }">Register</router-link>
                    </li>
                    <li>
                        <router-link :to="{ name: 'login' }">Login</router-link>
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
