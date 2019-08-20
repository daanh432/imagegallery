<template>
    <div>
        <div ref="ImageEditModal" uk-modal v-on:hidden="ResetEditForm">
            <div class="uk-modal-dialog uk-modal-body" v-if="selectedImageId != null">
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
                    <button @click="DeleteImage(selectedImageId)" class="uk-button uk-button-danger" type="button">Delete</button>
                    <button class="uk-button uk-button-muted uk-modal-close" type="button">Cancel</button>
                    <button @click="UpdateImage" class="uk-button uk-button-default" type="button">Save</button>
                </p>
            </div>
        </div>
        <div class="uk-grid-small uk-child-width-1-1 uk-child-width-1-3@m uk-child-width-1-4@l uk-child-width-1-5@xl" uk-grid uk-lightbox="animation: slide">
            <div :key="'imgContainer-' + image.id" class="imageContainer" v-for="(image, key) in images">
                <input class="uk-checkbox selectImageIcon" type="checkbox">
                <span @click="EditImage(image.id)" class="uk-icon-button uk-button-default editImageIcon" uk-icon="icon: pencil"></span>
                <lazy-component :key="'lazy-' + image.id" @show="ShowImage($event, image.thumbUrl)">
                    <a :data-caption="image.description != null ? image.description : ''" :href="image.url + '?token=' + $auth.token()" :key="'a-' + image.id" class="imageThumbnail">
                        <img :alt="image.name" :key="'img-' + image.id" :src="''" class="uk-width-1-1">
                    </a>
                </lazy-component>
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
</template>
<script>
    export default {
        props: {
            images: {
                type: Array
            },
            userId: {
                type: [String, Number]
            },
            meta: {
                type: Object
            }
        },

        data() {
            return {
                updated: false,
                selectedImageId: null,
                selectedImageBackup: null,
                selectBoxes: false,
            }
        },

        methods: {
            ShowImage(event, url) {
                let app = this;
                this.$nextTick(() => {
                    let image = event.$el.querySelector('img');
                    axios.get(url, {
                        responseType: 'arraybuffer',
                        headers: {
                            Authorization: `Bearer ${app.$auth.token()}`
                        }
                    }).then(response => {
                        image.src = 'data:image/jpeg;base64,' + new Buffer.from(response.data, 'binary').toString('base64');
                    }).catch(response => {
                        image.src = 'http://imagegallery.test/assets/img/placeholder.jpg';
                    });
                });
            },
            ResetEditForm() {
                this.updated === false ? this.images[this.selectedImageId] = this.selectedImageBackup : null;
                this.selectedImageId = null;
                this.selectedImageBackup = null;
            },
            EditImage(key) {
                this.selectedImageId = this.images.findIndex(obj => {
                    return obj.id === key;
                });
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
                    axios.post(`users/${this.userId}/images/${this.images[this.selectedImageId].id}`, data, {
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
            DeleteImage(key) {
                if (this.selectedImageId != null) {
                    let app = this;
                    window.UIkit.modal.confirm(`Are you sure you want to delete the image ${this.images[key].name}`).then(function () {
                        axios.post(`users/${app.userId}/images/${app.images[key].id}`, {
                            '_method': 'DELETE',
                            'Authorization': app.$auth.token()
                        }).then(response => {
                            app.images.splice(key, 1);
                            app.updated = true;
                            window.UIkit.notification({
                                message: 'Image deleted successfully',
                                status: 'success',
                                pos: 'bottom-center',
                                timeout: 2500
                            });
                        }).catch(response => {
                            app.updated = false;
                            window.UIkit.notification({
                                message: 'Something went wrong trying to delete this image. Please try again later or contact us',
                                status: 'danger',
                                pos: 'bottom-center',
                                timeout: 5000
                            });
                        });
                    }, function () {
                        // Do something or nothing if rejected
                    });
                }
            },
            GoToNext() {
                this.$router.push({
                    query: {
                        page: this.nextPage,
                    },
                });
            },
            GoToPrev() {
                this.$router.push({
                    query: {
                        page: this.prevPage,
                    }
                });
            },
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
        }
    }
</script>
