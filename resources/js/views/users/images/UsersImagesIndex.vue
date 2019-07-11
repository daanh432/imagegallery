<template>
    <div>
        <div ref="ImageEditModal" uk-modal v-on:hidden="ResetEditForm">
            <div class="uk-modal-dialog uk-modal-body" v-if="selectedImageId">
                <h2 class="uk-modal-title">Update Image Information</h2>
                <img :alt="images[selectedImageId].url" :src="images[selectedImageId].url">
                <form class="uk-form-blank">
                    <div class="uk-margin">
                        <label class="uk-form-label" for="name">Image Name</label>
                        <div class="uk-form-controls">
                            <input class="uk-input" id="name" name="Name" placeholder="The name of this image" type="text" v-model="images[selectedImageId].name">
                        </div>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label" for="description">Image Description</label>
                        <div class="uk-form-controls">
                            <textarea class="uk-textarea" id="description" name="Description" placeholder="Description of this image" rows="10" v-model="images[selectedImageId].description"></textarea>
                        </div>
                    </div>
                </form>

                <p class="uk-text-right">
                    <button class="uk-button uk-button-default uk-modal-close" type="button">Cancel</button>
                    <button @click="UpdateImage" class="uk-button uk-button-primary" type="button">Save</button>
                </p>
            </div>
        </div>

        <div class="uk-overflow-hidden">
            <div class="uk-container uk-background-primary uk-border-rounded uk-margin-large-top uk-padding">
                <h1 class="uk-text-center">Images</h1>
                <div v-if="!has_error">
                    <div class="uk-grid-small uk-child-width-1-1 uk-child-width-1-3@m uk-child-width-1-4@l uk-child-width-1-5@xl" uk-grid uk-lightbox="animation: slide">
                        <div class="imageContainer" v-for="(image, key) in images">
                            <span @click="EditImage(key)" class="uk-icon-button uk-button-default editImageIcon" uk-icon="icon: pencil"></span>
                            <a :data-caption="image.description" :href="image.url" class="imageThumbnail">
                                <v-lazy-image :alt="image.name" :src="image.url" class="uk-width-1-1"></v-lazy-image>
                            </a>
                        </div>
                    </div>
                    <div class="uk-text-center" uk-grid v-if="meta">
                        <div class="uk-width-1-3@m">
                            <button @click="goToPrev" class="uk-button uk-button-default" v-if="prevPage">Previous</button>
                        </div>
                        <div class="uk-width-1-3@m">
                            <p v-text="paginatonCount"></p>
                        </div>
                        <div class="uk-width-1-3@m">
                            <button @click="goToNext" class="uk-button uk-button-default" v-if="nextPage">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    const getImages = (params, token, callback) => {
        axios.get(`users/${params.userId}/images`, {
            params, headers: {
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
                updated: false,
                editing: false,
                selectedImageId: null,
                selectedImageBackup: null,
                has_error: false,
                images: [],
                meta: null
            }
        },

        computed: {
            nextPage() {
                if (!this.meta || this.meta.current_page >= this.meta.last_page) {
                    return;
                }
                return this.meta.current_page + 1;
            },
            prevPage() {
                if (!this.meta || this.meta.current_page <= 1) {
                    return;
                } else if (this.meta.current_page > this.meta.last_page) {
                    return this.meta.last_page;
                }
                return this.meta.current_page - 1;
            },
            paginatonCount() {
                if (!this.meta) {
                    return '0 of 0';
                }
                const {current_page, last_page} = this.meta;
                return `${current_page} of ${last_page}`;
            },
        },

        methods: {
            ResetEditForm() {
                this.updated === false ? this.images[this.selectedImageId] = this.selectedImageBackup : null;
                this.editing = false;
                this.selectedImageId = null;
                this.selectedImageBackup = null;
            },
            EditImage(key) {
                this.editing = true;
                this.selectedImageId = key;
                this.selectedImageBackup = Object.assign({}, this.images[this.selectedImageId]);
                window.UIkit.modal(this.$refs.ImageEditModal).show();
            },
            UpdateImage() {
                if (this.images[this.selectedImageId] != null) {
                    const token = this.$auth.token();
                    const data = {
                        'name': this.images[this.selectedImageId].name,
                        'description': this.images[this.selectedImageId].description,
                        '_method': 'PATCH'
                    };
                    axios.post(`users/${this.$auth.user().id}/images/${this.images[this.selectedImageId].id}`, data, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        }
                    }).then(response => {
                        window.UIkit.notification({
                            message: 'Image has been updated',
                            status: 'success',
                            pos: 'bottom-center',
                            timeout: 2500
                        });
                        this.updated = true;
                        window.UIkit.modal(this.$refs.ImageEditModal).hide();
                    }).catch(error => {
                        window.UIkit.notification({
                            message: 'Something went wrong when trying to update this image. Please try again later or contact us.',
                            status: 'danger',
                            pos: 'bottom-center',
                            timeout: 5000
                        });
                    });
                }
            },
            goToNext() {
                this.$router.push({
                    query: {
                        page: this.nextPage,
                    },
                });
            },
            goToPrev() {
                this.$router.push({
                    query: {
                        page: this.prevPage,
                    }
                });
            },
            setData(err, data) {
                if (err) {
                    this.has_error = true;
                    window.UIkit.notification({
                        message: 'Something went wrong when trying to fetch data. Please try again later or contact us.',
                        status: 'danger',
                        pos: 'bottom-center',
                        timeout: 5000
                    });
                } else {
                    this.has_error = false;
                    this.images = data.data;
                    this.meta = data.meta;
                }
            }
        },

        beforeRouteEnter(to, from, next) {
            let userId = to.params.userId != null ? to.params.userId : window.VueAPP.$auth.user().id;
            let token = window.VueAPP.$auth.token();
            let params = {userId, page: to.query.page};
            getImages(params, token, (err, data) => {
                next(vm => vm.setData(err, data));
            });
        },

        beforeRouteUpdate(to, from, next) {
            let userId = to.params.userId != null ? to.params.userId : this.$auth.user().id;
            let params = {userId, page: to.query.page};
            getImages(params, this.$auth.token(), (err, data) => {
                this.setData(err, data);
                next();
            });
        },
    }
</script>
