<template>
    <div class="uk-container">
        <div class="uk-align-center uk-card uk-card-default uk-card-hover uk-card-body uk-width-1-2@m uk-margin-large-top">
            <div class="uk-card-title">Register</div>
            <form autocomplete="off" @submit.prevent="register" method="post" class="uk-form-horizontal">
                <div class="uk-margin">
                    <label class="uk-form-label" for="name">Full Name</label>
                    <div class="uk-form-controls">
                        <input class="uk-input uk-border-rounded" id="name" name="name" type="text" placeholder="Your name" v-model="name" v-validate="'required'">
                        <span>{{ errors.first('name') }}</span>
                    </div>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label" for="email">Email</label>
                    <div class="uk-form-controls">
                        <input class="uk-input uk-border-rounded" id="email" name="email" type="text" placeholder="Your Email Address" v-model="email" v-validate="'required|email'">
                        <span>{{ errors.first('email') }}</span>
                    </div>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label" for="password">Password</label>
                    <div class="uk-form-controls">
                        <input class="uk-input uk-border-rounded" id="password" name="password" type="password" placeholder="Password" v-model="password" v-validate="'required|min:8'" ref="password">
                        <span>{{ errors.first('password') }}</span>
                    </div>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label" for="password_confirmation">Confirm your Password</label>
                    <div class="uk-form-controls">
                        <input class="uk-input uk-border-rounded" id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirm Password" v-model="password_confirmation" v-validate="'required|confirmed:password'">
                        <span>{{ errors.first('password_confirmation') }}</span>
                    </div>
                </div>
                <button type="submit" class="uk-button uk-button-default uk-align-center uk-margin-remove-bottom uk-margin-top">Register</button>
            </form>
        </div>
    </div>
</template>
<script>
    export default {
        data() {
            return {
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                success: false
            }
        },
        methods: {
            register() {
                this.$validator.validateAll().then();
                if (this.errors.any()) {
                    return window.UIkit.notification({
                        message: 'Please resolve validation errors before submitting.',
                        status: 'warning',
                        pos: 'bottom-center',
                        timeout: 2500
                    });
                }
                let app = this;
                this.$auth.register({
                    data: {
                        name: app.name,
                        email: app.email,
                        password: app.password,
                        password_confirmation: app.password_confirmation
                    },
                    success: function () {
                        app.success = true;
                        this.$router.push({name: 'login', params: {successRegistrationRedirect: true}})
                    },
                    error: function (res) {
                        if (res.response != null && res.response.data != null && res.response.data.errors != null && res.response.data.errors.email.indexOf('The email has already been taken.') !== -1) {
                            return window.UIkit.notification({
                                message: 'The email is already been taken. Please use a different email address or reset your password.',
                                status: 'warning',
                                pos: 'bottom-center',
                                timeout: 2500
                            });
                        }
                        return window.UIkit.notification({
                            message: 'Something went wrong during registration. Please try again later or contact us.',
                            status: 'danger',
                            pos: 'bottom-center',
                            timeout: 5000
                        });
                    }
                })
            }
        }
    }
</script>
