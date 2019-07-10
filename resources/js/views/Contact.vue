<template>
    <div class="uk-overflow-hidden">
        <div class="uk-container uk-background-primary uk-border-rounded uk-margin-large-top uk-margin-remove-top@s uk-padding">
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
                        <textarea class="uk-textarea uk-border-rounded" placeholder="Your Message" name="message" id="message" rows="10" v-model="message" v-validate="'required'"></textarea>
                        <span>{{ errors.first('message') }}</span>
                    </div>
                </div>

            </form>
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
            }
        }
    }
</script>
