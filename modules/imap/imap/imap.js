(function ($) {
    let workArea = $('#imapworkarea'),
        workAreaError = $('#imapworkareaError');

    workArea.show();
    workAreaError.hide();

    $(document).ready(function () {
        console.info('IMAP module loaded: FIX MEMORY LEAK 2026-05-25');

        try {
            app.imap.init();
        } catch (error) {
            console.error('IMAP init failed', error);
        }

        const forceOpenStreetMap = function () {
            if (!window.app || !app.imap || !app.imap.map || !app.imap.controls || !app.imap.controls.layers) {
                return;
            }

            Object.keys(app.imap.controls.layers._layers || {}).forEach(function (layerId) {
                const layer = app.imap.controls.layers._layers[layerId];

                if (layer && layer.name === 'OpenStreetMap' && layer.overlay !== true && !app.imap.map.hasLayer(layer.layer)) {
                    app.imap.map.addLayer(layer.layer);
                }
            });
        };

        document.cookie = "imap_layer=OpenStreetMap|*|; path=/";
        forceOpenStreetMap();
        setTimeout(forceOpenStreetMap, 500);
    });
})(jQuery);
