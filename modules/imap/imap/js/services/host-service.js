import {fetchHostList, searchHosts} from '../api/host';
import NotificationService from './notification';
import {__} from '../helpers';
import eventBus from './bus';
import {
    NOTIFY_HOST_FILTER_UPDATED,
    NOTIFY_HOST_REMOVED,
    NOTIFY_HOST_SUCCESSFULLY_LOADED,
    NOTIFY_HOST_UPDATED,
    NOTIFY_NEW_HOST_INFORMATION,
} from '../events';

import Host from '../models/host';

class HostService {
    constructor(updateInterval, {hardwareField, minStatus, autoRefresh = false}) {
        this.filterAvailableIds = [];
        this.lastFilterQuery = null;

        this.hardwareField = hardwareField;
        this.minStatus = minStatus;
        this.intervalId = null;
        this.updateInterval = updateInterval * 1000;
        this.autoRefresh = autoRefresh;

        this.hostList = {};

        this.loading = false;
        this.loaded = false;

        eventBus.on(NOTIFY_NEW_HOST_INFORMATION, (hostId, hostData) => this.refreshHost(hostId, hostData));
        eventBus.on(NOTIFY_HOST_FILTER_UPDATED, (query) => this.updateFilter(query));
    }

    stopTimeout() {
        if (this.intervalId) {
            clearTimeout(this.intervalId);
            this.intervalId = null;
        }
    }

    startTimout() {
        if (!this.autoRefresh) {
            return;
        }

        this.stopTimeout();

        this.intervalId = setTimeout(() => this.updateHostList({scheduleNext: true, force: true}), this.updateInterval);
    }

    updateHostList({scheduleNext = this.autoRefresh, force = false} = {}) {
        if (this.loading || (this.loaded && !force)) {
            return;
        }

        this.loading = true;
        this.stopTimeout();

        fetchHostList(this.hardwareField)
            .then(response => {
                const hostData = response.data;
                response.data = null;

                if (hostData.error) {
                    NotificationService.error(hostData.error.message, __('Hosts'));
                    return;
                }

                this.loaded = true;
                this.prepareHostList(hostData);
            })
            .finally(() => {
                if (scheduleNext) {
                    this.startTimout();
                }
                this.loading = false;
            });
    }

    refreshHost(hostId, newHostData) {
        newHostData.hostid = hostId;
        this.prepareHost(newHostData);
        eventBus.emit(NOTIFY_HOST_SUCCESSFULLY_LOADED, null, this.getFilteredHostList());
    }

    getFilteredHostList() {
        return Object.keys(this.hostList)
            .filter(hostId => !this.hostIsFiltered(hostId))
            .reduce((obj, key) => {
                obj[key] = this.hostList[key];
                return obj;
            }, {});
    }

    prepareHostList(newHostList) {
        let hostIdList = Object.keys(newHostList);

        // remove old hosts
        Object.keys(this.hostList)
            .filter(hostId => !hostIdList.includes(hostId))
            .forEach(hostId => {
                let host = this.hostList[hostId];
                delete this.hostList[hostId];
                eventBus.emit(NOTIFY_HOST_REMOVED, null, host);
            });


        hostIdList.forEach(hostId => this.prepareHost(newHostList[hostId]));

        eventBus.emit(NOTIFY_HOST_SUCCESSFULLY_LOADED, null, this.getFilteredHostList());

        if (Object.keys(this.hostList).length === 0) {
            NotificationService.info(__('No hosts with inventory'), null, 0);
        }
    }

    prepareHost(hostData) {
        let hostId = hostData.hostid;

        if (!this.hostList.hasOwnProperty(hostId)) {
            this.hostList[hostId] = new Host({
                hardwareField: this.hardwareField,
                minStatus: this.minStatus,
            });
        }

        const host = this.hostList[hostId];
        host.load(hostData);

        this.hostNotification(host);
    }

    hostIsFiltered(hostId) {
        return !!this.lastFilterQuery && !this.filterAvailableIds.includes(hostId);
    }

    hostNotification(host) {
        if (this.hostIsFiltered(host.hostid)) {
            eventBus.emit(NOTIFY_HOST_REMOVED, null, host);
        } else {
            eventBus.emit(NOTIFY_HOST_UPDATED, null, host);
        }
    }

    updateFilter(query) {
        query = query.toLocaleString().trim();
        if (query === '') {
            query = null;
        }

        if (query === this.lastFilterQuery) {
            return;
        }

        this.lastFilterQuery = query;

        if (query === '') {
            this.filterAvailableIds = [];
        } else {
            searchHosts(query)
                .then((response) => {
                    if (this.lastFilterQuery !== query) {
                        return;
                    }

                    this.filterAvailableIds = Object.keys(response.data);

                    Object.values(this.hostList).forEach(host => this.hostNotification(host));

                    eventBus.emit(NOTIFY_HOST_SUCCESSFULLY_LOADED, null, this.getFilteredHostList());
                });
        }
    }
}

export default HostService;
