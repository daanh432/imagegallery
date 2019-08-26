<template>
    <div>
        <form id="dragAndDropForm" ref="fileform" v-if="!has_error">
            <div class="uk-overflow-hidden">
                <div class="uk-container uk-background-primary uk-border-rounded uk-margin-large-top uk-padding">
                    <h1 class="uk-text-center">Images</h1>
                    <div class="uk-text-center uk-margin-bottom">
                        <div uk-form-custom>
                            <input multiple type="file" v-on:change="UploadDialog($event)">
                            <button class="uk-button uk-button-small uk-button-default" tabindex="-1" type="button"><span uk-icon="icon: push"></span> Upload Images</button>
                        </div>
                    </div>
                    <div>
                        <Images :images="sortedImages" :inputAuthorized="true" :meta="meta" :token="$auth.token()" :userId="userId"></Images>
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

    import ImageApi from '../../../api/images';
    import Images from '../../../components/Images';

    export default {
        components: {
            Images: Images
        },

        data() {
            return {
                userId: null,
                has_error: false,
                images: [],
                meta: null,
                dragAndDropCapable: false,
                files: [],
                runningUploads: 0
            }
        },

        computed: {
            sortedImages() {
                return this.images != null && this.images.length > 0 ? this.images.sort((a, b) => {
                    if (a.timestamp < b.timestamp)
                        return 1;
                    if (a.timestamp > b.timestamp)
                        return -1;
                    return 0;
                }) : [];
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
                console.log(file.lastModifiedDate);
                formData.append('date', file.lastModified);
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
                    this.images = data.data;
                    this.meta = data.meta;
                }
            }
        },

        beforeRouteEnter(to, from, next) {
            let token = window.localStorage.getItem('ImageGallery-Auth-Token');
            let userId = to.params.userId != null ? to.params.userId : null;
            let params = {userId, page: to.query.page};
            ImageApi.GetImages(params, token, (err, data) => {
                next(vm => {
                    vm.SetData(err, data);
                });
            });
        },

        beforeRouteUpdate(to, from, next) {
            let userId = to.params.userId != null ? to.params.userId : this.$auth.user().id;
            let params = {userId, page: to.query.page};
            ImageApi.GetImages(params, this.$auth.token(), (err, data) => {
                this.SetData(err, data);
                next();
            });
        }
        ,

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
        }
        ,
    }
</script>
