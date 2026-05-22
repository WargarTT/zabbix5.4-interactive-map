class HostMarker extends L.Marker {

    /**
     *
     * @param iconCreator
     * @param tooltipCreator
     * @param popupCreator {PopupCreator}
     */
    constructor({iconCreator, tooltipCreator, popupCreator}) {
        super();

        this.isMaintenance = false;
        this.isTriggered = false;
        this.hostName = null;
        this.hardware = null;
        this.iconState = null;
        this.tooltipState = null;
        this.iconChanged = false;
        this.currentHost = null;

        this.iconCreator = iconCreator;
        this.tooltipCreator = tooltipCreator;
        this.popupCreator = popupCreator;
        this.popup = null;

        this.on('click', () => this.openPopup());
        this.on('mouseover', () => {
            if (!this.tooltipCreator.showMarkersLabels) {
                this.ensureTooltip();
                this.openTooltip();
            }
        });
        this.on('mouseout', () => {
            if (!this.tooltipCreator.showMarkersLabels && this._tooltip) {
                this.closeTooltip();
            }
        });

        // TODO: this.on('move', () => eventBus.emit('update-lines-marker', null, this.hostId));
    }

    updateByHost(host) {
        this.currentHost = host;

        this.updateHostInfo(host)
            .updatePositionByHost(host)
            .updateView();
    }

    updateHostInfo(host) {
        this.status = host.getStatus();
        this.isMaintenance = host.isInMaintenance();
        this.isTriggered = host.isWasTriggered();
        this.hostName = host.name;
        this.hardware = host.hardware;

        return this;
    }

    updatePositionByHost(host) {
        let nextLat = host.inventory.location_lat;
        let nextLng = host.inventory.location_lon;

        let currentLat, currentLng;
        if (this._preSpiderfyLatlng) {
            currentLat = this._preSpiderfyLatlng.lat;
            currentLng = this._preSpiderfyLatlng.lng;
        } else {
            currentLat = this._latlng ? this._latlng.lat : null;
            currentLng = this._latlng ? this._latlng.lng : null;
        }

        if (currentLat !== nextLat || currentLng !== nextLng) {
            this.setLatLng([nextLat, nextLng]);
        }

        return this;
    }

    updateView() {
        this.iconChanged = false;

        if (this.tooltipCreator.showMarkersLabels || this._tooltip) {
            this.ensureTooltip();
        }

        const iconState = [
            this.status,
            this.isMaintenance,
            this.isTriggered,
            this.hardware,
        ].join('|');

        if (this.iconState !== iconState) {
            this.setIcon(this.iconCreator(this));
            this.iconState = iconState;
            this.iconChanged = true;
        }

        return this;
    }

    ensureTooltip() {
        if (!this._tooltip) {
            this.bindTooltip(this.tooltipCreator.buildEmptyTooltip());
        }

        const tooltipState = [
            this.status,
            this.hostName,
            this.hardware,
        ].join('|');

        if (this.tooltipState !== tooltipState) {
            this._tooltip.updateContent(this);
            this.tooltipState = tooltipState;
        }

        return this;
    }

    ensurePopup() {
        if (!this.popup) {
            this.popup = this.popupCreator.create();
            this._popup = L.popup().setContent(this.popup.element);
            this._popup.on('remove', () => this.popup.onCloseHostPopup());
        }

        return this;
    }

    openPopup() {
        if (!this.currentHost || !this._map) {
            return this;
        }

        this.ensurePopup();
        this._popup.setLatLng(this.getLatLng());
        this._popup.openOn(this._map);
        this.popup.onOpenHostPopup(this, this.currentHost);

        return this;
    }

    closePopup() {
        if (this._map && this._popup) {
            this._map.closePopup(this._popup);
        }

        return this;
    }

    updateIconByParent() {
        let marker = this;

        while (marker) {
            marker = marker.__parent;
            if (marker) {
                marker._updateIcon();
                if (marker.__iconObj) {
                    marker.setIcon(marker.__iconObj);
                }
            }
        }
    }
}

export default HostMarker;
