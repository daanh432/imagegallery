<template>
    <div class="uk-overflow-hidden">
        <div class="uk-container uk-background-primary uk-border-rounded uk-margin-large-top uk-padding uk-width-1-2@l">
            <div v-if="!has_error">
                <h1 class="uk-text-center">Welcome {{ user.name }}</h1>

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
                user: []
            }
        },

        methods: {
            setData(err, data) {
                if (err) {
                    this.has_error = true;
                    this.$router.push({
                        name: 'home',
                    });
                } else {
                    this.has_error = false;
                    this.user = data.data;
                }
            }
        },

        beforeRouteEnter(to, from, next) {
            let userId = undefined;
            if (to.params != null && to.params.userId != null) {
                userId = to.params.userId;
            } else {
                userId = window.VueAPP.$auth.user().id;
            }
            let token = window.localStorage.getItem('ImageGallery-Auth-Token');
            getUser(userId, token, (err, data) => {
                next(vm => vm.setData(err, data));
            });
        },

        beforeRouteUpdate(to, from, next) {
            let userId = undefined;
            if (to.params != null && to.params.userId != null) {
                userId = to.params.userId;
            } else {
                userId = this.$auth.user().id;
            }
            getUser(userId, this.$auth.token(), (err, data) => {
                this.setData(err, data);
                next();
            });
        },
    }
</script>
