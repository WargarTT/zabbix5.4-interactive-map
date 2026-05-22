import filter from '../models/filter';
import {deleteRequest, getRequest, postRequest} from './index';

let linkListCache = null;
let linkListRequest = null;

export const fetchLinkList = () => {
    const params = {
        hostid: filter.hostId,
        groupid: filter.groupId,
    };

    if (linkListCache) {
        return Promise.resolve({data: linkListCache});
    }

    if (!linkListRequest) {
        linkListRequest = getRequest('ajax/links', params)
            .then(response => {
                linkListCache = response.data;
                response.data = null;

                return linkListCache;
            })
            .finally(() => {
                linkListRequest = null;
            });
    }

    return linkListRequest.then(data => ({data: data}));
};

export const createLink = (hostId, targetHosts) => {
    linkListCache = null;

    return postRequest('ajax/links/create', {thostId: targetHosts}, {hostid: hostId});
};

export const updateLink = (linkId, linkOptions) => {
    linkListCache = null;

    return postRequest('ajax/links/update', {linkoptions: linkOptions}, {linkid: linkId});
};

export const deleteLink = (linkId) => {
    linkListCache = null;

    return deleteRequest('ajax/links/delete', {linkid: linkId});
};
