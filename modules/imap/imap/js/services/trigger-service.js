import {fetchTriggerList} from '../api/trigger';
import {__} from '../helpers';
import Trigger from '../models/trigger';
import eventBus from './bus';
import {NOTIFY_TRIGGER_REMOVED, NOTIFY_TRIGGER_UPDATED} from '../events';
import NotificationService from './notification';
import TriggerEvent from '../models/trigger-event';

class TriggerService {
    constructor(intervalTimeout) {
        this.intervalId = null;
        this.intervalTimeout = intervalTimeout * 1000;
        this.triggerList = {};
        this.triggerStates = {};
        this.hostList = {};

        this.loading = false;
    }

    refreshHostList(hostList) {
        this.hostList = hostList;

        return this;
    }

    stopInterval() {
        if (this.intervalId) {
            clearTimeout(this.intervalId);
            this.intervalId = null;
        }

        return this;
    }

    startInterval() {
        this.stopInterval();
        this.intervalId = setTimeout(() => this.updateTriggerList(), this.intervalTimeout);

        return this;
    }


    updateTriggerList() {
        if (this.loading) {
            return;
        }

        this.loading = true;

        this.stopInterval();

        fetchTriggerList()
            .then(response => {
                const triggerData = response.data;
                response.data = null;

                if (triggerData.error) {
                    NotificationService.error(triggerData.error.message, __('Triggers'));
                    return;
                }

                this.prepareTriggerList(triggerData);
            })
            .finally(() => {
                this.startInterval();
                this.loading = false;
            });
    }

    prepareTriggerList(newTriggerList) {
        let triggerIdList = Object.keys(newTriggerList);

        // remove old triggers
        Object.keys(this.triggerList)
            .filter(triggerId => !triggerIdList.includes(triggerId))
            .forEach(triggerId => {
                let trigger = this.triggerList[triggerId];
                trigger.hosts.forEach(host => host.removeTrigger(triggerId));
                delete this.triggerList[triggerId];
                delete this.triggerStates[triggerId];
                eventBus.emit(NOTIFY_TRIGGER_REMOVED, null, trigger);
            });

        triggerIdList.forEach(triggerId => {
            let triggerData = newTriggerList[triggerId];
            const triggerState = this.getTriggerState(triggerData);

            if (this.triggerStates[triggerId] === triggerState) {
                return;
            }

            if (this.triggerList.hasOwnProperty(triggerId)) {
                this.triggerList[triggerId].hosts.forEach(host => host.removeTrigger(triggerId));
            } else {
                this.triggerList[triggerId] = new Trigger();
            }

            // Prepare hosts by host list
            triggerData.hosts = triggerData.hosts
                .filter(hostData => this.hostList.hasOwnProperty(hostData.hostid))
                .map(hostData => this.hostList[hostData.hostid]);

            if(triggerData.lastEvent) {
                triggerData.lastEvent = new TriggerEvent().load(triggerData.lastEvent);
            }

            const trigger = this.triggerList[triggerId];
            trigger.load(triggerData);
            this.triggerStates[triggerId] = triggerState;

            triggerData.hosts.forEach(host => host.appendTrigger(trigger));

            eventBus.emit(NOTIFY_TRIGGER_UPDATED, null, trigger);
        });
    }

    getTriggerState(triggerData) {
        const hosts = Array.isArray(triggerData.hosts)
            ? triggerData.hosts.map(host => host.hostid).sort().join(',')
            : '';

        const lastEvent = triggerData.lastEvent || {};

        return [
            triggerData.triggerid,
            triggerData.status,
            triggerData.value,
            triggerData.priority,
            triggerData.lastchange,
            triggerData.description,
            hosts,
            lastEvent.eventid || '',
            lastEvent.acknowledged || '',
            lastEvent.severity || '',
        ].join('|');
    }
}

export default TriggerService;
