import axios from 'axios';

export default {

    GetAlbum(params, token, callback) {
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
