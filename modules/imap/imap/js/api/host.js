import filter from '../models/filter';
import {getRequest, postRequest} from './index';

const hostListCache = {};
const hostListRequests = {};

export const fetchHostList = (hardwareField) => {
    const params = {
        hostid: filter.hostId,
        groupid: filter.groupId,
        hardwareField: hardwareField
    };
    const cacheKey = JSON.stringify(params);

    if (hostListCache.hasOwnProperty(cacheKey)) {
        return Promise.resolve({data: hostListCache[cacheKey]});
    }

    if (!hostListRequests.hasOwnProperty(cacheKey)) {
        hostListRequests[cacheKey] = getRequest('ajax/hosts', params)
            .then(response => {
                hostListCache[cacheKey] = response.data;
                response.data = null;

                return hostListCache[cacheKey];
            })
            .finally(() => {
                delete hostListRequests[cacheKey];
            });
    }

    return hostListRequests[cacheKey].then(data => ({data: data}));
};

export const searchHosts = (query) => {
    return getRequest('ajax/hosts/search', {
        hostid: filter.hostId,
        groupid: filter.groupId,
        query: query,
    });
};

export const fetchFullHostInfo = (hostId) => {
    return getRequest('ajax/hosts/view', {hostid: hostId});
};

export const updateHostLocation = (hostId, lat, lng) => {
    return postRequest('ajax/hosts/update-location', {lat: lat, lng: lng}, {hostid: hostId});
};
