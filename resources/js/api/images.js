import axios from 'axios';

export default {
    GetImages(params, token, callback) {
        axios.get(`users/${params.userId}/images`, {
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
