<template>
    <div class="uk-overflow-hidden">
        <div class="uk-container uk-background-primary uk-border-rounded uk-margin-large-top uk-padding uk-width-1-2@l">
            <div v-if="!has_error && user != null">
                <h1 class="uk-text-center">Welcome {{ user.name }}</h1>
                <p>View your
                    <router-link :to="ImageRoute">Images</router-link>
                </p>
                <p>View your
                    <router-link :to="AlbumRoute">Albums</router-link>
                </p>
                <form v-if="user != null" @submit.prevent="UpdateUser">
                    <div class="uk-margin">
                        <input class="uk-input" type="password" placeholder="Current Password" v-model="currentPassword">
                    </div>
                    <div class="uk-margin">
                        <input class="uk-input" type="text" placeholder="Your name" v-validate="'required|max:200   '" v-model="updateUser.name" name="name">
                        <span>{{ errors.first('name') }}</span>
                    </div>
                    <div class="uk-margin">
                        <input class="uk-input" type="text" placeholder="Your email address" v-validate="'required|max:200|email'" v-model="updateUser.email" name="email">
                        <span>{{ errors.first('email') }}</span>
                    </div>
                    <button type="submit" class="uk-button uk-button-default uk-align-center uk-margin-remove-bottom uk-margin-top" :disabled="loading">Save</button>
                </form>
            </div>
            <div v-if="has_error">
                <h1 class="uk-text-center">You're not authorized to view this users information</h1>
            </div>
        </div>
    </div>
</template>
<script>
    const getUser = (userId, token, callback) => {
        axios.get(`users/${userId}`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        }).then(response => {
            callback(null, response.data);
        }).catch(error => {
            callback(error, error.response.data);
        });
    };

    export default {
        data() {
            return {
                has_error: false,
                currentPassword: '',
                user: null,
                updateUser: null,
                loading: false,
            }
        },

        methods: {
            UpdateUser() {
                this.loading = true;
                this.$validator.validateAll().then(() => {
                    if (this.errors.any()) {
                        this.loading = false;
                        return window.UIkit.notification({
                            message: 'Please resolve validation errors before submitting.',
                            status: 'warning',
                            pos: 'bottom-center',
                            timeout: 2500
                        });
                    }
                    let config = {
                        '_method': 'PATCH',
                        name: this.updateUser.name,
                        email: this.updateUser.email,
                        currentPassword: this.currentPassword,
                    };

                    axios.post(`users/${this.user.id}`, config).then(response => {
                        this.currentPassword = '';
                        this.user = response.data.data;
                        this.updateUser = $.extend({}, response.data.data);
                        this.loading = false;
                    }).catch(error => {
                        this.currentPassword = '';
                        this.loading = false;
                        app.has_error = true;
                        window.UIkit.notification({
                            message: 'Your credentials do not match any known accounts.',
                            status: 'danger',
                            pos: 'bottom-center',
                            timeout: 5000
                        });
                    });
                });
            },
            SetData(err, data) {
                if (err) {
                    this.has_error = true;
                    this.$router.push({
                        name: 'home',
                    });
                } else {
                    this.has_error = false;
                    this.user = data.data;
                    this.updateUser = $.extend({}, data.data);
                }
            }
        },

        computed: {
            ImageRoute() {
                let originalId = this.$route.params.userId + '';
                return {name: 'users.images.index', params: {userId: originalId}};
            },
            AlbumRoute() {
                let originalId = this.$route.params.userId + '';
                return {name: 'users.albums.index', params: {userId: originalId}};
            }
        },

        beforeRouteEnter(to, from, next) {
            let userId = undefined;
            if (to.params != null && to.params.userId != null) {
                userId = to.params.userId;
            }
            let token = window.localStorage.getItem('ImageGallery-Auth-Token');
            getUser(userId, token, (err, data) => {
                next(vm => vm.SetData(err, data));
            });
        },

        beforeRouteUpdate(to, from, next) {
            let userId = undefined;
            if (to.params != null && to.params.userId != null) {
                userId = to.params.userId;
            }
            getUser(userId, this.$auth.token(), (err, data) => {
                this.SetData(err, data);
                next();
            });
        },
    }
</script>
