import {__} from '../helpers';
import eventBus from './bus';
import {deleteLink, fetchLinkList, updateLink} from '../api/link';
import {
    NOTIFY_LINK_LAYER_UPDATED,
    NOTIFY_LINK_REMOVED,
    NOTIFY_LINK_UPDATED,
    OPEN_LINK_OPTIONS_DIALOG,
    RECEIVED_LINKS_DATA
} from '../events';

import NotificationService from './notification';
import Link from '../models/link';
import LinkOptions from '../views/link-options';

class LinkService {

    constructor() {
        this.loadingVersion = 0;
        this.loading = false;
        this.loaded = false;

        this.linkList = {};
        this.hostList = {};

        eventBus.on(NOTIFY_LINK_LAYER_UPDATED, () => this.rerenderLinks());
        eventBus.on(OPEN_LINK_OPTIONS_DIALOG, (linkId) => this.openLinkOptionsDialog(linkId));
        eventBus.on(RECEIVED_LINKS_DATA, (linkListData) => this.receivedLinkData(linkListData))

        /*
        TODO: link interval ?
app.imap.vars.intervalTriggersID = window.setInterval(function () {
        loadLinks();
    }, app.imap.settings.intervalLoadLinks * 1000);
         */
    }

    refreshHostList(hostList) {
        this.hostList = hostList;

        return this;
    }

    updateLinkList({force = false} = {}) {
        if (this.loading || (this.loaded && !force)) {
            return;
        }

        this.loading = true;
        const version = ++this.loadingVersion;

        fetchLinkList()
            .then(response => {
                if (version !== this.loadingVersion) {
                    return;
                }

                const linkData = response.data;
                response.data = null;

                if (linkData.error) {
                    NotificationService.error(linkData.error.message, __('Links'));
                    return;
                }

                this.loaded = true;
                this.prepareLinkList(linkData);
            })
            .finally(() => {
                this.loading = false;
            });
    }

    removeLink(linkId) {
        if (this.linkList.hasOwnProperty(linkId)) {
            let link = this.linkList[linkId];
            delete this.linkList[linkId];
            eventBus.emit(NOTIFY_LINK_REMOVED, null, link);
        }
    }

    prepareLinkList(newLinkList) {
        let linkIdList = Object.keys(newLinkList);

        // remove old links
        Object.keys(this.linkList)
            .filter(linkId => !linkIdList.includes(linkId))
            .forEach(linkId => this.removeLink(linkId));

        Object.values(this.linkList)
            .filter(link => !link.host1 || !link.host2 || !this.hostList.hasOwnProperty(link.host1.hostid) || !this.hostList.hasOwnProperty(link.host2.hostid))
            .forEach(link => this.removeLink(link.id));

        linkIdList.forEach(linkId => {
            let linkData = newLinkList[linkId];
            this.prepareLink(linkData);
        });

        this.rerenderLinks();
    }

    prepareLink(linkData) {
        if (!linkData.host1 || !linkData.host2 || !this.hostList.hasOwnProperty(linkData.host1) || !this.hostList.hasOwnProperty(linkData.host2)) {
            if (this.linkList.hasOwnProperty(linkData.id)) {
                delete this.linkList[linkData.id];
            }
            return;
        }

        linkData.host1 = this.hostList[linkData.host1];
        linkData.host2 = this.hostList[linkData.host2];

        if (!this.linkList.hasOwnProperty(linkData.id)) {
            this.linkList[linkData.id] = new Link();
        }

        const link = this.linkList[linkData.id];

        link.load(linkData);
    }

    receivedLinkData(linkList) {
        this.loaded = true;
        linkList.forEach(linkData => this.prepareLink(linkData));
        this.rerenderLinks();
    }

    rerenderLinks() {
        Object.values(this.linkList).forEach(link => {
            eventBus.emit(NOTIFY_LINK_UPDATED, null, link);
        });
    }

    /* Изменение свойств линии связи */
    openLinkOptionsDialog(linkId) {
        if (!this.linkList.hasOwnProperty(linkId)) {
            NotificationService.error(__('Link not found'));
            return;
        }

        let link = this.linkList[linkId];

        new LinkOptions({
            link: link,
            onUpdateLink: (linkData) => {
                updateLink(linkId, linkData).then((response) => {
                    if (response.data.error) {
                        NotificationService.error(response.data.error);
                        return;
                    }

                    NotificationService.info(__('Successful'));

                    this.prepareLink(response.data);
                    this.rerenderLinks();
                });
            },
            onDeleteLink: () => {
                deleteLink(linkId).then((response) => {
                    if (response.data.error) {
                        NotificationService.error(response.data.error);
                        return;
                    }

                    NotificationService.info(__('Successful'));

                    this.removeLink(linkId);
                });
            },
        }).open();
    }
}

export default LinkService;
