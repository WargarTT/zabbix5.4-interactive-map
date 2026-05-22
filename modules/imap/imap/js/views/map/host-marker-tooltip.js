class HostMarkerTooltip extends L.Tooltip {
    constructor({showMarkersLabels, useIconsInMarkers}) {
        super({
            offset: [-12, 0],
            direction: 'left',
            permanent: showMarkersLabels,
        });

        this.useIconsInMarkers = useIconsInMarkers;
    }

    /**
     * @param marker {HostMarker}
     */
    updateContent(marker) {
        let labelContent = L.DomUtil.create('div', '');
        if (this.useIconsInMarkers) {
            const normalizedHardware = HostMarkerTooltip.normalizeHardware(marker.hardware);
            const hasHardware = !!normalizedHardware;
            const imageUrl = hasHardware
                ? `modules/imap/imap/hardware/${normalizedHardware}.png`
                : `modules/imap/imap/images/status${marker.status}.gif`;
            let wrapper = L.DomUtil.create('span', hasHardware ? 'marker-tooltip-icon marker-tooltip-icon-hardware' : 'marker-tooltip-icon');
            let image = L.DomUtil.create('span', hasHardware ? 'marker-tooltip-hardware-image' : 'marker-tooltip-status-image');
            image.style.backgroundImage = `url('${imageUrl}')`;
            wrapper.append(image);

            labelContent.append(wrapper);
        }
        labelContent.append(marker.hostName);

        this.setContent(labelContent);
    }

    static normalizeHardware(hardware) {
        if (!hardware || hardware === 'undefined' || hardware === 'none') {
            return null;
        }

        return `${hardware}`.replace(/[^a-zA-Z0-9_-]/g, '');
    }
}

export default HostMarkerTooltip;
