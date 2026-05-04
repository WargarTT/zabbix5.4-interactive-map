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
            const failBackIcon = `modules/imap/imap/images/status${marker.status}.gif`;
            const hasHardware = marker.hardware && marker.hardware !== 'undefined' && marker.hardware !== 'none';
            let wrapper = L.DomUtil.create('span', hasHardware ? 'marker-tooltip-icon marker-tooltip-icon-hardware' : 'marker-tooltip-icon');
            let image = new Image();
            image.className = hasHardware ? 'marker-tooltip-hardware-image' : 'marker-tooltip-status-image';

            image.onerror = () => {
                image.src = failBackIcon;
                image.className = 'marker-tooltip-status-image';
                wrapper.className = 'marker-tooltip-icon';
            };
            image.src = hasHardware ? `modules/imap/imap/hardware/${marker.hardware}.png` : failBackIcon;
            wrapper.append(image);

            labelContent.append(wrapper);
        }
        labelContent.append(marker.hostName);

        this.setContent(labelContent);
    }
}

export default HostMarkerTooltip;
