<template>
    <div>
        <ul :style="{top: menu.top, left: menu.left}" @blur="CloseMenu" id="right-click-menu" ref="rightClickMenu" tabindex="-1" v-if="menu.view">
            <li @click.stop="RemoveFromAlbum(menu.id)" v-if="inAlbum != null">Remove Photo From Album</li>
            <li @click.stop="EditImage(menu.id)">Edit Photo</li>
            <li @click.stop="DeleteImage(menu.id)">Delete Photo</li>
        </ul>
        <div ref="ImageEditModal" uk-modal v-on:hidden="ResetEditForm">
            <div class="uk-modal-dialog uk-modal-body" v-if="selectedImageId != null">
                <h2 class="uk-modal-title">Update Image Information</h2>
                <img :alt="images[selectedImageId].url" :src="AddTokenToUrl(images[selectedImageId].url)">
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
            <div :key="keyPrefix + '-imgContainer-' + image.id" @click.right="OpenMenu($event, image.id)" class="imageContainer" v-for="(image, key) in images">
                <input :checked="currentSelectedImages.includes(image.id)" @click="SelectImage($event, image.id)" class="uk-checkbox selectImageIcon" type="checkbox" v-if="selectBoxes">
                <span @click="EditImage(image.id)" class="uk-icon-button uk-button-default editImageIcon" uk-icon="icon: pencil" v-if="!selectBoxes && authorized"></span>
                <a :data-caption="image.description != null ? image.description : ''" :href="AddTokenToUrl(image.url)" :key="keyPrefix + '-a-' + image.id" class="imageThumbnail">
                    <lazy-component :key="keyPrefix + '-lazy-' + image.id + '-' + image.name">
                        <img :alt="image.name" :key="keyPrefix + '-img-' + image.id" :src="AddTokenToUrl(image.thumbUrl)" class="uk-width-1-1">
                    </lazy-component>
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
            },
            inAlbum: {},
            enableSelection: {
                type: Boolean
            },
            currentSelectedImages: {
                type: Array
            },
            inputKeyPrefix: {
                type: String
            },
            inputAuthorized: {
                type: Boolean,
                required: true,
            },
            token: {
                required: true,
            }
        },

        data() {
            return {
                authorized: this.inputAuthorized != null ? this.inputAuthorized : false,
                menu: {
                    top: 0,
                    left: 0,
                    id: null,
                    view: false,
                    loading: false,
                },
                updated: false,
                selectedImageId: null,
                selectedImageBackup: null,
                selectBoxes: this.enableSelection != null ? this.enableSelection : false,
                selectedImages: this.currentSelectedImages != null ? this.currentSelectedImages : [],
            }
        },

        methods: {
            SetMenu(top, left) {
                top = window.scrollY + top - 25;
                let largestWidth = window.innerWidth - this.$refs.rightClickMenu.offsetWidth - 25;
                if (left > largestWidth) left = largestWidth;
                this.menu.top = top + 'px';
                this.menu.left = left + 'px';
            },
            OpenMenu(e, id) {
                if (this.menu.loading === false && this.authorized) {
                    this.menu.view = true;
                    this.menu.id = id;
                    this.menu.top = window.scrollY + 10 + 'px';
                    this.$nextTick(() => {
                        this.$refs.rightClickMenu.focus();
                        this.SetMenu(e.y, e.x)
                    });
                }
                e.preventDefault();
            },
            CloseMenu() {
                this.menu.view = false;
            },
            SelectImage(event, imageId) {
                if (event.target.checked) {
                    this.selectedImages.push(imageId);
                } else {
                    this.selectedImages.splice(this.selectedImages.findIndex(value => value === imageId), 1);
                }
                this.$emit('selectionChange', this.selectedImages);
            },
            /**
             * @return {string}
             */
            AddTokenToUrl(url) {
                let returnAnswer = `${url}?token=${this.token}`;
                this.inAlbum != null ? returnAnswer += `&albumId=${this.inAlbum}` : null;
                return returnAnswer;
            },
            ResetEditForm() {
                this.updated === false ? this.images[this.selectedImageId] = this.selectedImageBackup : null;
                this.selectedImageId = null;
                this.selectedImageBackup = null;
            },
            EditImage(key) {
                this.menu.view = false;
                this.selectedImageId = this.images.findIndex(obj => {
                    return obj.id === key;
                });
                this.selectedImageBackup = Object.assign({}, this.images[this.selectedImageId]);
                window.UIkit.modal(this.$refs.ImageEditModal).show();
            },
            UpdateImage() {
                if (this.images[this.selectedImageId] != null) {
                    const token = this.token;
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
            DeleteImage(imageId) {
                if (imageId != null) {
                    let key = this.images.findIndex(image => image.id === imageId);
                    let app = this;
                    this.menu.view = false;
                    window.UIkit.modal.confirm(`Are you sure you want to delete the image ${this.images[key].name} permanently? This deletes the image entirely! Not just from an album.`).then(function () {
                        axios.post(`users/${app.userId}/images/${app.images[key].id}`, {
                            '_method': 'DELETE',
                            'Authorization': app.token
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
            RemoveFromAlbum(imageId) {
                if (this.authorized) {
                    this.$emit('removeImageFromAlbum', imageId);
                    this.menu.view = false;
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
            keyPrefix() {
                return this.inputKeyPrefix != null ? this.inputKeyPrefix : 'AKuUyfsDCt78tDsah4HNofS8Jyn67ofUdtdah4H8n67toCDCtf';
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
        }
    }
</script>

<style scoped>
    #right-click-menu {
        background: #FAFAFA;
        border: 1px solid #BDBDBD;
        box-shadow: 0 2px 2px 0 rgba(0, 0, 0, .14), 0 3px 1px -2px rgba(0, 0, 0, .2), 0 1px 5px 0 rgba(0, 0, 0, .12);
        display: block;
        list-style: none;
        margin: 0;
        padding: 0;
        position: absolute;
        width: 275px;
        z-index: 999999;
    }

    #right-click-menu li {
        border-bottom: 1px solid #E0E0E0;
        margin: 0;
        padding: 5px 35px;
    }

    #right-click-menu li:last-child {
        border-bottom: none;
    }

    #right-click-menu li:hover {
        background: #1E88E5;
        color: #FAFAFA;
    }
</style>
