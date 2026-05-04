import axios from 'axios';

const prepareParams = (route, params) => {
    return Object.assign({}, params, {
        r: route,
        output: 'ajax',
    });
};

export const getRequest = (route, params) => {
    return axios.get('zabbix.php?action=imap.ajax', {params: prepareParams(route, params)});
};

export const postRequest = (route, data, params) => {
    return axios.post('zabbix.php?action=imap.ajax', data, {params: prepareParams(route, params)});
};

export const deleteRequest = (route, params) => {
    return axios.delete('zabbix.php?action=imap.ajax', {params: prepareParams(route, params)});
};