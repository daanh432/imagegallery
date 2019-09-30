<template>
    <div class="uk-overflow-hidden">
        <div class="uk-container uk-background-primary uk-border-rounded uk-margin-large-top uk-padding uk-width-1-2@l" v-if="!success">
            <h1 class="uk-text-center">Contact</h1>
            <form class="uk-form-horizontal uk-margin-large">

                <div class="uk-margin">
                    <label class="uk-form-label" for="fullName">Full Name</label>
                    <div class="uk-form-controls">
                        <input class="uk-input uk-border-rounded" id="fullName" name="fullName" type="text" placeholder="Full Name" v-model="fullName" v-validate="'required'">
                        <span>{{ errors.first('fullName') }}</span>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="emailAddress">Email</label>
                    <div class="uk-form-controls">
                        <input class="uk-input uk-border-rounded" id="emailAddress" name="emailAddress" type="text" placeholder="Email" v-model="emailAddress" v-validate="'required|email'">
                        <span>{{ errors.first('emailAddress') }}</span>
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="message">Your Message</label>
                    <div class="uk-form-controls">
                        <textarea class="uk-textarea uk-border-rounded" id="message" name="message" placeholder="Your Message" rows="10" v-model="message" v-validate="'required|max:8096'"></textarea>
                        <span>{{ errors.first('message') }}</span>
                    </div>
                </div>
                <div class="uk-margin">
                    <div class="uk-form-controls">
                        <button @click.prevent="sendMessage" class="uk-button uk-button-default uk-border-rounded uk-align-center uk-margin-remove-bottom" type="submit">Send Message
                            <div uk-spinner v-if="loading"></div>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="uk-container uk-background-primary uk-border-rounded uk-margin-large-top uk-margin-remove-top@s uk-padding" v-else>
            <h1 class="uk-text-center">Your message has been send.</h1>
        </div>
    </div>
</template>
<script>
    export default {
        data() {
            return {
                fullName: '',
                emailAddress: '',
                message: '',
                success: false,
                loading: false,
            }
        },
        methods: {
            sendMessage() {
                if (this.loading === false) {
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

                        let app = this;

                        axios.post('contact', {
                            name: this.fullName,
                            email: this.emailAddress,
                            message: this.message
                        }).then((data) => {
                            window.UIkit.notification({
                                message: 'Message has been delivered.',
                                status: 'success',
                                pos: 'bottom-center',
                                timeout: 10000
                            });
                            app.success = true;
                            app.loading = false;
                        }).catch((data) => {
                            window.UIkit.notification({
                                message: 'Something went wrong trying to send your message. Please try again later.',
                                status: 'danger',
                                pos: 'bottom-center',
                                timeout: 5000
                            });
                            app.loading = false;
                        });
                    });
                }
            }
        }
    }
</script>
