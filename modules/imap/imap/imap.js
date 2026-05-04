(function ($) {
    let workArea = $('#imapworkarea'),
        workAreaError = $('#imapworkareaError');

    workArea.show();
    workAreaError.hide();

    $(document).ready(function () {
        try {
            app.imap.init();
        } catch (error) {
            console.error('IMAP init failed, fallback markers will still be attempted', error);
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

        const loadFallbackHostMarkers = function () {
            if (!window.app || !app.imap || !app.imap.map || !window.L) {
                console.warn('IMAP fallback: map is not ready yet');
                return false;
            }

            $.getJSON('zabbix.php?action=imap.ajax&r=ajax/hosts')
                .done(function (hosts) {
                    if (app.imap.markers && Object.keys(app.imap.markers.markerList || {}).length > 0) {
                        console.info('IMAP fallback: skipped because native host markers are loaded', Object.keys(app.imap.markers.markerList).length);
                        return;
                    }

                    const markers = [];
                    const layer = L.layerGroup();

                    Object.keys(hosts || {}).forEach(function (hostId) {
                        const host = hosts[hostId];
                        const inventory = host.inventory || {};
                        const lat = parseFloat(String(inventory.location_lat || '').replace(',', '.'));
                        const lng = parseFloat(String(inventory.location_lon || '').replace(',', '.'));

                        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                            return;
                        }

                        const marker = L.circleMarker([lat, lng], {
                            radius: 7,
                            color: '#1f7a1f',
                            fillColor: '#2fa84f',
                            fillOpacity: 0.9,
                            weight: 2
                        }).bindPopup(host.name || host.hostid);

                        markers.push(marker);
                        layer.addLayer(marker);
                    });

                    if (markers.length === 0) {
                        console.warn('IMAP fallback: ajax returned hosts, but no hosts had valid coordinates', hosts);
                        return;
                    }

                    if (app.imap.fallbackHostLayer) {
                        app.imap.map.removeLayer(app.imap.fallbackHostLayer);
                    }

                    app.imap.fallbackHostLayer = layer;
                    layer.addTo(app.imap.map);

                    if (app.imap.controls && app.imap.controls.layers) {
                        app.imap.controls.layers.addOverlay(layer, 'Hosts fallback');
                    }

                    if (!app.imap._fallbackHostFitDone) {
                        app.imap.map.fitBounds(L.featureGroup(markers).getBounds().pad(0.2));
                        app.imap._fallbackHostFitDone = true;
                    }

                    console.info('IMAP fallback: added host markers', markers.length);
                })
                .fail(function (xhr, status, error) {
                    console.error('IMAP fallback: failed to load hosts', status, error, xhr.responseText);
                });

            return true;
        };

        document.cookie = "imap_layer=OpenStreetMap|*|; path=/";
        forceOpenStreetMap();
        setTimeout(forceOpenStreetMap, 500);

        let attempts = 0;
        const fallbackInterval = setInterval(function () {
            attempts++;

            if (loadFallbackHostMarkers() || attempts >= 10) {
                clearInterval(fallbackInterval);
            }
        }, 1000);
    });
})(jQuery);
