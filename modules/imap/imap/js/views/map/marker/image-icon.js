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

        const normalizedHardware = ImageIcon.normalizeHardware(hardware);
        const hasHardware = !!normalizedHardware;
        const imageUrl = hasHardware
            ? `modules/imap/imap/hardware/${normalizedHardware}.png`
            : `modules/imap/imap/images/status${status}.gif`;

        const html = [
            `<span class="${hasHardware ? 'marker-icon-wrap marker-icon-wrap-hardware' : 'marker-icon-wrap'}">`,
            `<span class="${hasHardware ? 'marker-hardware-image' : 'marker-status-image'}" style="background-image:url('${imageUrl}')"></span>`,
            '</span>',
        ].join('');

        super({
            className: classNames.join(' '),
            html: html,
            iconAnchor: [8, 8]
        });
    }

    static normalizeHardware(hardware) {
        if (!hardware || hardware === 'undefined' || hardware === 'none') {
            return null;
        }

        return `${hardware}`.replace(/[^a-zA-Z0-9_-]/g, '');
    }

}

export default ImageIcon;
