<template>
    <div class="uk-overflow-hidden">
        <div class="uk-container uk-background-primary uk-border-rounded uk-margin-large-top uk-padding uk-width-1-2@l">
            <h1 class="uk-text-center">Users</h1>
            <div class="uk-overflow-auto">
                <table class="uk-table uk-table-striped uk-table-hover uk-table-small uk-table-justify">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr @click="goToUser(user.id)" v-for="(user, key) in users">
                        <td>{{ user.name }}</td>
                        <td>{{ user.email }}</td>
                        <td>{{ user.role }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="uk-text-center" uk-grid>
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
</template>
<script>
    const getUsers = (page, token, callback) => {
        const params = {page};
        axios.get('users', {
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
                has_error: false,
                users: [],
                meta: []
            }
        },

        methods: {
            goToUser(userId) {
                this.$router.push({
                    name: 'users.show',
                    params: {
                        'userId': userId
                    }
                });
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
                    this.users = data.data;
                    this.meta = data.meta;
                }
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

        beforeRouteEnter(to, from, next) {
            const params = {
                page: to.query.page
            };
            let token = window.localStorage.getItem('ImageGallery-Auth-Token');
            getUsers(to.query.page, token, (err, data) => {
                next(vm => vm.setData(err, data));
            });
        },

        beforeRouteUpdate(to, from, next) {
            getUsers(to.query.page, this.$auth.token(), (err, data) => {
                this.setData(err, data);
                next();
            });
        },
    }
</script>
