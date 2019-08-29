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
                                <input class="uk-input" id="name" name="Name" placeholder="Name of this album" type="text" v-model="form.name" v-validate="'required|max:250'">
                                <span>{{ errors.first('Name') }}</span>
                            </div>
                        </div>
                        <div class="uk-margin">
                            <label class="uk-form-label" for="name">Album Description</label>
                            <div class="uk-form-controls">
                                <textarea class="uk-textarea" id="description" name="Description" placeholder="Description of this album" rows="10" v-model="form.description" v-validate="'required|max:2000'"></textarea>
                                <span>{{ errors.first('Description') }}</span>
                            </div>
                        </div>

                        <div class="uk-margin uk-grid-small uk-child-width1-1 uk-child-width-1-3@s uk-grid">
                            <label @click="form.access_level = 0"><input :checked="form.access_level === 0" class="uk-radio" name="accessLevel" type="radio"> Only for my eyes</label>
                            <label @click="form.access_level = 1"><input :checked="form.access_level === 1" class="uk-radio" name="accessLevel" type="radio"> Password Protected</label>
                            <label @click="form.access_level = 2"><input :checked="form.access_level === 2" class="uk-radio" name="accessLevel" type="radio"> Publicly accessible</label>
                        </div>

                        <div class="uk-margin" v-if="form.access_level === 1">
                            <label class="uk-form-label" for="name">Album Password</label>
                            <div class="uk-form-controls">
                                <input class="uk-input" id="password" name="Password" placeholder="Public password for this album" type="text" v-model="form.access_password">
                            </div>
                        </div>
                    </form>

                    <p class="uk-text-right">
                        <button v-if="updating === true && form.id != null" @click="DeleteAlbum(form.id)" class="uk-button uk-button-danger" type="button">Delete</button>
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
                        <div :key="'album-' + album.id" class="imageContainer" v-for="(album, key) in albums">
                            <span :key="'album-' + album.id + '-edit-button'" @click="EditAlbum(album.id)" class="uk-icon-button uk-button-default editImageIcon" uk-icon="icon: pencil"></span>
                            <router-link :key="'album-' + album.id + '-link'" :to="{ name: 'users.albums.show', params: {userId: userId, albumId: album.id}}" class="imageThumbnail">
                                <lazy-component :key="'lazy-' + album.id + album.randomImage.name + album.name">
                                    <img :alt="'Random Image From' + album.randomImage.name" :key="'img-' + album.id" :src="AddTokenToUrl(album.randomImage.thumbUrl)" class="uk-width-1-1">
                                    <p :key="'album-' + album.id + '-name'" class="uk-text-center uk-margin-remove-top">{{ album.name }}</p>
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
                    id: null,
                    name: null,
                    description: null,
                    access_level: null,
                    access_password: null,
                },
                userId: null,
                has_error: false,
                albums: [],
                meta: null,
                updating: false,
                creating: false,
                selectedAlbum: null,
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
            DeleteAlbum(albumId) {
                let app = this;
                let key = this.albums.findIndex(album => album.id === albumId);
                window.UIkit.modal.confirm(`Are you sure you want to delete ${this.albums[key].name} permanently? This deletes the album permanently! This will not delete the images`).then(function () {
                    axios.post(`users/${app.userId}/albums/${albumId}`, {
                        '_method': 'DELETE',
                        headers: {}
                    }).then(response => {
                        app.albums.splice(key, 1);
                        window.UIkit.modal(app.$refs.AlbumModal).hide();
                    }).catch(response => {
                        window.UIkit.notification({
                            message: 'Something went wrong when trying to create this album. Please try again later or contact us.',
                            status: 'danger',
                            pos: 'bottom-center',
                            timeout: 5000
                        });
                    });
                }, function () {
                    // Do something or nothing if rejected
                });
            },
            EditAlbum(albumId) {
                this.form = $.extend({}, this.albums.find(album => album.id === albumId));
                window.UIkit.modal(this.$refs.AlbumModal).show();
                this.updating = true;
            },
            CreateAlbum() {
                window.UIkit.modal(this.$refs.AlbumModal).show();
                this.creating = true;
                this.form.name = null;
                this.form.description = null;
                this.form.access_level = 0;
                this.form.access_password = null;
            },
            ResetForm() {
                this.form.name = '';
                this.form.description = '';
                this.form.access_level = 0;
                this.form.access_password = null;
                this.form.id = null;
                this.creating = false;
                this.updating = false;
            },
            SaveAlbum() {
                this.$validator.validateAll().then(() => {
                    if (this.errors.any()) {
                        return window.UIkit.notification({
                            message: 'Please resolve validation errors before submitting.',
                            status: 'warning',
                            pos: 'bottom-center',
                            timeout: 2500
                        });
                    }
                    if (this.creating) {
                        let app = this;
                        axios.post(`users/${this.userId}/albums`, {
                            name: this.form.name,
                            description: this.form.description,
                            access_level: this.form.access_level,
                            access_password: this.form.access_password,
                            headers: {}
                        }).then(response => {
                            this.albums.push(response.data.data);
                            window.UIkit.modal(this.$refs.AlbumModal).hide();
                        }).catch(response => {
                            window.UIkit.notification({
                                message: 'Something went wrong when trying to create this album. Please try again later or contact us.',
                                status: 'danger',
                                pos: 'bottom-center',
                                timeout: 5000
                            });
                        });
                    } else if (this.updating) {
                        let key = this.albums.findIndex(album => album.id === this.form.id);
                        if (key !== -1 || key != null) {
                            axios.post(`users/${this.userId}/albums/${this.form.id}`, {
                                name: this.form.name,
                                description: this.form.description,
                                access_level: this.form.access_level,
                                access_password: this.form.access_password,
                                '_method': 'PATCH'
                            }).then(response => {
                                this.albums[key] = Object.assign({}, response.data.data);
                                this.albums = Array.from(this.albums);
                                window.UIkit.modal(this.$refs.AlbumModal).hide();
                            }).catch(response => {
                                window.UIkit.notification({
                                    message: 'Something went wrong when trying to update this album. Please try again later or contact us.',
                                    status: 'danger',
                                    pos: 'bottom-center',
                                    timeout: 5000
                                });
                            });
                        }
                    }
                });
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
            /**
             * @return {string}
             */
            AddTokenToUrl(url) {
                let returnAnswer = `${url}?token=${this.$auth.token()}`;
                this.inAlbum != null ? returnAnswer += `&albumId=${this.inAlbum}` : null;
                return returnAnswer;
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
