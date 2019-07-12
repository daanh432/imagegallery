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

        <form id="dragAndDropForm" ref="fileform" v-if="!has_error"></form>

        <div class="uk-overflow-hidden" v-if="!has_error">
            <div class="uk-container uk-background-primary uk-border-rounded uk-margin-large-top uk-padding">
                <h1 class="uk-text-center">Images</h1>
                <div class="uk-text-center uk-margin-bottom">
                    <div uk-form-custom>
                        <input multiple type="file" v-on:change="UploadDialog($event)">
                        <button class="uk-button uk-button-small uk-button-default" tabindex="-1" type="button"><span uk-icon="icon: push"></span> Upload Images</button>
                    </div>
                </div>
                <div>
                    <div class="uk-grid-small uk-child-width-1-1 uk-child-width-1-3@m uk-child-width-1-4@l uk-child-width-1-5@xl" uk-grid uk-lightbox="animation: slide">
                        <div class="imageContainer" v-for="(image, key) in reversedItems">
                            <span @click="EditImage(image.id)" class="uk-icon-button uk-button-default editImageIcon" uk-icon="icon: pencil"></span>
                            <a :data-caption="image.description != null ? image.description : ''" :href="image.url" class="imageThumbnail">
                                <v-lazy-image :alt="image.name" :src="image.thumbUrl" class="uk-width-1-1"></v-lazy-image>
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
        <div v-else>
            <div class="uk-container uk-background-primary uk-border-rounded uk-margin-large-top uk-padding">
                <h1 class="uk-text-center">An error occurred during load. Please try again later.</h1>
            </div>
        </div>
    </div>
</template>
<script>
    const determineDragAndDropCapable = () => {
        let div = document.createElement('div');
        return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
    };

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
                userId: null,
                dragAndDropCapable: false,
                has_error: false,
                images: [],
                meta: null,
                updated: false,
                selectedImageId: null,
                selectedImageBackup: null,
                files: [],
                uploadPercentage: 0,
                runningUploads: 0
            }
        },

        computed: {
            reversedItems() {
                return this.images.slice().reverse();
            },
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

        watch: {
            runningUploads() {
                if (this != null && this.runningUploads != null && this.runningUploads < 4 && this.files.length > 0) {
                    this.UploadImage();
                }
            }
        },

        methods: {
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
            UploadImage() {
                const file = this.files[0];
                let formData = new FormData();
                formData.append('newImage', file);
                this.files.splice(0, 1);
                if (file.type === 'image/png' || file.type === 'image/jpeg' || file.type === 'image/jpg') {
                    this.runningUploads = this.runningUploads + 1;
                    let app = this;
                    axios.post(`users/${this.userId}/images`, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                            'Authorization': `Bearer ${this.$auth.token()}`
                        }
                    }).then(response => {
                        if (app.files.length === 0 && app.runningUploads <= 1) {
                            window.UIkit.notification({
                                message: 'Images have been uploaded.',
                                status: 'success',
                                pos: 'bottom-center',
                                timeout: 2500
                            });
                        }
                        app.images.push(response.data.data);
                        app.runningUploads = app.runningUploads - 1;
                    }).catch(response => {
                        if (app.files.length === 0) {
                            window.UIkit.notification({
                                message: 'Images have been uploaded with errors.',
                                status: 'warning',
                                pos: 'bottom-center',
                                timeout: 2500
                            });
                        }
                        app.runningUploads = app.runningUploads - 1;
                    });
                } else {
                    window.UIkit.notification({
                        message: `${file.name.split('.').pop()} is not supported. Only png, jpg, jpeg are supported.`,
                        status: 'warning',
                        pos: 'bottom-right',
                        timeout: 0
                    });
                    this.UploadImage();
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
            },
            UploadDialog(e) {
                let form = e.target;
                for (let i = 0; i < form.files.length; i++) {
                    this.files.push(form.files[i]);
                }
                // Upload instantly
                this.UploadImage();
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

        mounted() {
            this.userId = this.$route != null && this.$route.params != null && this.$route.params.userId != null ? this.$route.params.userId : window.VueAPP.$auth.user().id;
            this.dragAndDropCapable = determineDragAndDropCapable();
            if (this.dragAndDropCapable) {
                ['drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop'].forEach(function (evt) {
                    this.$refs.fileform.addEventListener(evt, function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }.bind(this), false);
                }.bind(this));

                this.$refs.fileform.addEventListener('drop', function (e) {
                    for (let i = 0; i < e.dataTransfer.files.length; i++) {
                        this.files.push(e.dataTransfer.files[i]);
                    }
                    // Upload instantly
                    this.UploadImage();
                }.bind(this));
            }
        },
    }
</script>
