<template>
    <div class="uk-container">
        <div class="uk-align-center uk-card uk-card-default uk-card-hover uk-card-body uk-width-1-2@m uk-margin-large-top">
            <div class="uk-card-title">Login</div>
            <form autocomplete="off" @submit.prevent="login" method="post" class="uk-form-horizontal">
                <div class="uk-margin">
                    <label class="uk-form-label" for="email">Email</label>
                    <div class="uk-form-controls">
                        <input class="uk-input uk-border-rounded" id="email" name="email" type="text" placeholder="Email" v-model="email" v-validate="'required|email'">
                        <span>{{ errors.first('email') }}</span>
                    </div>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label" for="password">Password</label>
                    <div class="uk-form-controls">
                        <input class="uk-input uk-border-rounded" id="password" name="password" type="password" placeholder="Password" v-model="password" v-validate="'required'">
                        <span>{{ errors.first('password') }}</span>
                        <!--                        <a class="uk-link-muted" href="/reset">Reset Password</a>-->
                    </div>
                </div>
                <button type="submit" class="uk-button uk-button-default uk-align-center uk-margin-remove-bottom uk-margin-top">Login</button>
            </form>
        </div>
    </div>
</template>
<script>
    export default {
        data() {
            return {
                email: null,
                password: null,
                has_error: false
            }
        },
        methods: {
            login() {
                this.$validator.validateAll().then();
                if (this.errors.any()) {
                    return window.UIkit.notification({
                        message: 'Please resolve validation errors before submitting.',
                        status: 'warning',
                        pos: 'bottom-center',
                        timeout: 2500
                    });
                }
                // get the redirect object
                let redirect = this.$auth.redirect();
                let app = this;
                this.$auth.login({
                    params: {
                        email: app.email,
                        password: app.password
                    },
                    success: function () {
                        app.has_error = false;
                        const redirectTo = redirect ? redirect.from.name : 'home';
                        this.$router.push({name: redirectTo})
                    },
                    error: function () {
                        app.has_error = true;
                        window.UIkit.notification({
                            message: 'Your credentials do not match any known accounts.',
                            status: 'danger',
                            pos: 'bottom-center',
                            timeout: 5000
                        });
                    },
                    rememberMe: true,
                    fetchUser: true
                })
            }
        }
    }
</script>
