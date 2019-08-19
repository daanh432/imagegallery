<template>
    <div>
        <form id="dragAndDropForm" ref="fileform" v-if="!has_error">
            <div class="uk-overflow-hidden">
                <div class="uk-container uk-background-primary uk-border-rounded uk-margin-large-top uk-padding">
                    <h1 class="uk-text-center">Images from the album {{ this.AlbumName }}</h1>
                    <div class="uk-text-center uk-margin-bottom">
                        <div uk-form-custom>
                            <input multiple type="file" v-on:change="UploadDialog($event)">
                            <button class="uk-button uk-button-small uk-button-default" tabindex="-1" type="button"><span uk-icon="icon: push"></span> Upload Images</button>
                        </div>
                    </div>
                    <div>
                        <Images :images="this.reversedItems" :meta="null" :userId="userId"></Images>
                    </div>
                </div>
            </div>
        </form>
        <div v-if="has_error">
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

    import Images from '../../../components/Images';

    const getAlbum = (params, token, callback) => {
        axios.get(`users/${params.userId}/albums/${params.albumId}`, {
            page: params.page,
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
        components: {
            Images: Images
        },

        data() {
            return {
                userId: null,
                has_error: false,
                album: null,
                dragAndDropCapable: false,
                files: [],
                runningUploads: 0
            }
        },

        computed: {
            reversedItems() {
                return this.album != null && Object.entries(this.album).length !== 0 && this.album.constructor === Object && this.album.images != null && this.album.images.constructor === Array ? this.album.images.slice().reverse() : [];
            },
            /**
             * @return {string}
             */
            AlbumName() {
                return this.album != null && Object.entries(this.album).length !== 0 && this.album.constructor === Object ? this.album.name : 'Unknown';
            }
        },

        watch: {
            runningUploads() {
                if (this != null && this.runningUploads != null && this.runningUploads < 4 && this.files.length > 0) {
                    this.UploadImage();
                }
            }
        },

        methods: {
            UploadImage() {
                const file = this.files[0];
                let formData = new FormData();
                formData.append('newImage', file);
                formData.append('albumId', this.album.id);
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
                        app.album.images.push(response.data.data);
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
            UploadDialog(e) {
                let form = e.target;
                for (let i = 0; i < form.files.length; i++) {
                    this.files.push(form.files[i]);
                }
                // Upload instantly
                this.UploadImage();
            },
            SetData(err, data) {
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
                    this.album = data.data;
                }
            }
        },

        mounted() {
            this.userId = this.$route != null && this.$route.params != null && this.$route.params.userId != null ? this.$route.params.userId : this.$auth.user().id;
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

        beforeRouteEnter(to, from, next) {
            next(vm => {
                vm.$auth.fetch({
                    params: {},
                    success: function () {
                        let userId = to.params.userId != null ? to.params.userId : vm.$auth.user().id;
                        let params = {userId, page: to.query.page, albumId: to.params.albumId};
                        getAlbum(params, vm.$auth.token(), (err, data) => {
                            vm.SetData(err, data);
                        });
                    },
                    error: function () {
                        vm.has_error = true;
                    },
                });
            });
        },

        beforeRouteUpdate(to, from, next) {
            let userId = to.params.userId != null ? to.params.userId : this.$auth.user().id;
            let params = {userId, page: to.query.page, albumId: to.params.albumId};
            getAlbum(params, this.$auth.token(), (err, data) => {
                this.SetData(err, data);
                next();
            });
        },
    }
</script>
