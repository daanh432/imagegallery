<template>
    <div>
        <div ref="AlbumModal" uk-modal v-on:hidden="ResetForm">
            <div class="uk-modal-dialog uk-modal-body">
                <div v-if="creating === true || updating === true">
                    <h2 class="uk-modal-title" v-if="creating">Creating Album</h2>
                    <h2 class="uk-modal-title" v-if="updating">Update Album Information</h2>
                    <form class="uk-form-blank">
                        <div class="uk-margin">
                            <label class="uk-form-label" for="name">Album Name</label>
                            <div class="uk-form-controls">
                                <input class="uk-input" id="name" name="Name" placeholder="Name of this album" type="text" v-model="form.name">
                            </div>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="name">Album Description</label>
                            <div class="uk-form-controls">
                                <textarea class="uk-textarea" id="description" name="Description" placeholder="Description of this album" rows="10" v-model="form.description"></textarea>
                            </div>
                        </div>
                    </form>

                    <p class="uk-text-right">
                        <button class="uk-button uk-button-muted uk-modal-close" type="button">Cancel</button>
                        <button @click="SaveAlbum" class="uk-button uk-button-default" type="button">Save</button>
                    </p>
                </div>
                <!--                <div v-if="updating">-->
                <!--                    <h2 class="uk-modal-title">Update Album Information</h2>-->
                <!--                    <form class="uk-form-blank">-->
                <!--                        <div class="uk-margin">-->
                <!--                            <label class="uk-form-label" for="name">Image Name</label>-->
                <!--                            <div class="uk-form-controls">-->
                <!--                                <input class="uk-input" id="name" name="Name" placeholder="The name of this image" type="text" v-model="images[selectedImageId].name">-->
                <!--                            </div>-->
                <!--                        </div>-->

                <!--                        <div class="uk-margin">-->
                <!--                            <label class="uk-form-label" for="description">Image Description</label>-->
                <!--                            <div class="uk-form-controls">-->
                <!--                                <textarea class="uk-textarea" id="description" name="Description" placeholder="Description of this image" rows="10" v-model="images[selectedImageId].description"></textarea>-->
                <!--                            </div>-->
                <!--                        </div>-->
                <!--                    </form>-->

                <!--                    <p class="uk-text-right">-->
                <!--                        <button @click="DeleteImage(selectedImageId)" class="uk-button uk-button-danger" type="button">Delete</button>-->
                <!--                        <button class="uk-button uk-button-muted uk-modal-close" type="button">Cancel</button>-->
                <!--                        <button @click="UpdateImage" class="uk-button uk-button-default" type="button">Save</button>-->
                <!--                    </p>-->
                <!--                </div>-->
            </div>
        </div>

        <div class="uk-overflow-hidden" v-if="!has_error">
            <div class="uk-container uk-background-primary uk-border-rounded uk-margin-large-top uk-padding">
                <h1 class="uk-text-center">Albums</h1>
                <button @click.prevent="CreateAlbum" class="uk-button uk-button-small uk-button-default" type="button"><span uk-icon="icon: push"></span> New Album</button>
                <div>
                    <div class="uk-grid-small uk-child-width-1-1 uk-child-width-1-3@m uk-child-width-1-4@l uk-child-width-1-5@xl" uk-grid uk-lightbox="animation: slide">
                        <div class="albumContainer" v-for="(album, key) in reversedItems">
                            {{ album.name }}
                            <!--                            <span @click="EditAlbum(image.id)" class="uk-icon-button uk-button-default editImageIcon" uk-icon="icon: pencil"></span>-->
                            <!--                            <a :data-caption="image.description != null ? image.description : ''" :href="image.url" class="imageThumbnail">-->
                            <!--                                <v-lazy-image :alt="image.name" :src="image.thumbUrl" class="uk-width-1-1"></v-lazy-image>-->
                            <!--                            </a>-->
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
    const getAlbums = (params, token, callback) => {
        axios.get(`users/${params.userId}/albums`, {
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
        data() {
            return {
                form: {
                    name: null,
                    description: null,
                },
                userId: null,
                has_error: false,
                albums: [],
                meta: null,
                updating: false,
                creating: false,
            }
        },

        computed: {
            reversedItems() {
                return this.albums.slice().reverse();
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

        methods: {
            EditAlbum(key) {

            },
            CreateAlbum() {
                let albumId = this.albums.findIndex(obj => {
                    return obj.id === key;
                });
                window.UIkit.modal(this.$refs.AlbumModal).show();
                this.creating = true;
            },
            ResetForm() {
                this.name = null;
                this.description = null;
            },
            SaveAlbum() {
                if (this.creating) {
                    //
                } else if (this.updating) {
                    //
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
            next(vm => {
                vm.$auth.fetch({
                    params: {},
                    success: function () {
                        let userId = to.params.userId != null ? to.params.userId : vm.$auth.user().id;
                        let params = {userId, page: to.query.page};
                        getAlbums(params, vm.$auth.token(), (err, data) => {
                            vm.setData(err, data);
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
            let params = {userId, page: to.query.page};
            getAlbums(params, this.$auth.token(), (err, data) => {
                this.setData(err, data);
                next();
            });
        },

        mounted() {
            this.userId = this.$route != null && this.$route.params != null && this.$route.params.userId != null ? this.$route.params.userId : window.VueAPP.$auth.user().id;
        },
    }
</script>
