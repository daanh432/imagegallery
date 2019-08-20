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
            </div>
        </div>

        <div class="uk-overflow-hidden" v-if="!has_error">
            <div class="uk-container uk-background-primary uk-border-rounded uk-margin-large-top uk-padding">
                <h1 class="uk-text-center">Albums</h1>
                <div class="uk-text-center uk-margin-bottom">
                    <button @click.prevent="CreateAlbum" class="uk-button uk-button-small uk-button-default" type="button"><span uk-icon="icon: push"></span> New Album</button>
                </div>
                <div>
                    <div class="uk-grid-small uk-child-width-1-1 uk-child-width-1-2@m uk-child-width-1-3@l uk-child-width-1-4@xl" uk-grid>
                        <div :key="album.id" class="imageContainer" v-for="(album, key) in reversedItems">
                            <span @click="EditAlbum(album.id)" class="uk-icon-button uk-button-default editImageIcon" uk-icon="icon: pencil"></span>
                            <router-link :to="{ name: 'albums.show', params: {albumId: album.id}}" class="imageThumbnail">
                                <lazy-component :key="'lazy-' + album.id" @show="ShowImage($event, album.randomImage.thumbUrl)">
                                    <img :alt="'Random Image From' + album.randomImage.name" :key="'img-' + album.id" class="uk-width-1-1">
                                    <p class="uk-text-center uk-margin-remove-top">{{ album.name }}</p>
                                </lazy-component>
                            </router-link>
                        </div>
                    </div>
                    <div class="uk-text-center" uk-grid v-if="meta">
                        <div class="uk-width-1-3@m">
                            <button @click="GoToPrev" class="uk-button uk-button-default" v-if="prevPage">Previous</button>
                        </div>
                        <div class="uk-width-1-3@m">
                            <p v-text="paginatonCount"></p>
                        </div>
                        <div class="uk-width-1-3@m">
                            <button @click="GoToNext" class="uk-button uk-button-default" v-if="nextPage">Next</button>
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
    import AlbumApi from '../../../api/albums';

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
                let albumId = this.albums.findIndex(obj => {
                    return obj.id === key;
                });
            },
            CreateAlbum() {
                window.UIkit.modal(this.$refs.AlbumModal).show();
                this.creating = true;
            },
            ResetForm() {
                this.form.name = null;
                this.form.description = null;
                this.creating = false;
            },
            SaveAlbum() {
                if (this.creating) {
                    let app = this;
                    axios.post(`users/${this.userId}/albums`, {
                        name: this.form.name,
                        description: this.form.description,
                        headers: {}
                    }).then(response => {
                        this.albums.push(response.data.data);
                        window.UIkit.modal(this.$refs.ImageEditModal).hide();
                    }).catch(response => {
                        window.UIkit.notification({
                            message: 'Something went wrong when trying to create this album. Please try again later or contact us.',
                            status: 'danger',
                            pos: 'bottom-center',
                            timeout: 5000
                        });
                    });
                } else if (this.updating) {
                    axios.post('').then(response => {

                    }).catch(response => {
                        window.UIkit.notification({
                            message: 'Something went wrong when trying to update this album. Please try again later or contact us.',
                            status: 'danger',
                            pos: 'bottom-center',
                            timeout: 5000
                        });
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
                    this.albums = data.data;
                    this.meta = data.meta;
                }
            },
            ShowImage(event, url) {
                let app = this;
                if (url.startsWith('https://')) {
                    this.$nextTick(() => {
                        let image = event.$el.querySelector('img');
                        image.src = url;
                    });
                } else {
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
                }
            },
        },

        beforeRouteEnter(to, from, next) {
            next(vm => {
                vm.$auth.fetch({
                    params: {},
                    success: function () {
                        let userId = to.params.userId != null ? to.params.userId : vm.$auth.user().id;
                        let params = {userId, page: to.query.page};
                        AlbumApi.GetAlbums(params, vm.$auth.token(), (err, data) => {
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
            let params = {userId, page: to.query.page};
            AlbumApi.GetAlbums(params, this.$auth.token(), (err, data) => {
                this.SetData(err, data);
                next();
            });
        },

        mounted() {
            this.userId = this.$route != null && this.$route.params != null && this.$route.params.userId != null ? this.$route.params.userId : this.$auth.user().id;
        },
    }
</script>
