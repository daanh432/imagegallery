import axios from 'axios';

export default {

    GetAlbum(params, token, callback) {
        let config = {
            params: {
                page: params.page,
                access_password: params.access_password,
            },
            headers: {
                'Authorization': `Bearer ${token}`
            }
        };

        axios.get(`users/${params.userId}/albums/${params.albumId}`, config).then(response => {
            callback(null, response.data);
        }).catch(error => {
            callback(error, error.response.data);
        });
    },

    GetAlbums(params, token, callback) {
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
    }

}
