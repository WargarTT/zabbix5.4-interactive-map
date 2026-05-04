class ImageIcon extends L.DivIcon {
    constructor({
                    isTriggered,
                    isMaintenance,
                    status,
                    hardware,
                }) {
        let classNames = [
            'icon-status-img',
            `icon-status-${status}`,
        ];

        if (!isTriggered) {
            classNames.push('not-trigger');
        }

        if (isMaintenance) {
            classNames.push('maintenance');
        }

        const statusGif = `modules/imap/imap/images/status${status}.gif`;
        const hasHardware = hardware && hardware !== 'undefined' && hardware !== 'none';
        let wrapper = L.DomUtil.create('div', hasHardware ? 'marker-icon-wrap marker-icon-wrap-hardware' : 'marker-icon-wrap');
        let image = new Image();
        image.className = hasHardware ? 'marker-hardware-image' : 'marker-status-image';

        image.onerror = () => {
            image.src = statusGif;
            image.className = 'marker-status-image';
            wrapper.className = 'marker-icon-wrap';
        };

        image.src = hasHardware ? `modules/imap/imap/hardware/${hardware}.png` : statusGif;
        wrapper.append(image);

        super({
            className: classNames.join(' '),
            html: wrapper,
            iconAnchor: [8, 8]
        });
    }

}

export default ImageIcon;
