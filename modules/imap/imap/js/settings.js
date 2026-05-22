export const DEFAULT_SETTINGS = {
    lang: null,

    startBaseLayer: null,

    /* Settings changed in interactive mode. */
    doMapControl: false,
    pauseMapControl: false,
    showWithTriggersOnly: false,
    minStatus: null,

    /* Defaults that can be overridden from settings.js in module root. */
    showIcons: true,
    useSearch: true,
    useZoomSlider: true,
    linksEnabled: true,
    debugEnabled: true,
    hardwareField: 'type',
    maxMarkersSpiderfy: 50,
    excludingInventory: ['hostid', 'location_lat', 'location_lon', 'url_a', 'url_b', 'url_c', 'inventory_mode'],
    useIconsInMarkers: true,
    startCoordinates: [54.8720, 69.1450],
    startZoom: 13,
    mapAnimation: true,
    hostUpdateInterval: 60,
    triggerUpdateInterval: 30,
    intervalLoadLinks: 60,
    showMarkersLabels: false,
    spiderfyDistanceMultiplier: 1,
    defaultBaseLayer: 'OpenStreetMap',
    weatherApiKey: '-',
    bingApiKey: false,

    staffApi: {
        enable: false,
        url: null,
        refreshInterval: 60,
    },
};

export const ZOOM_METERS = [
    1000000,
    500000,
    300000,
    100000,
    50000,
    20000,
    10000,
    5000,
    2000,
    1000,
    500,
    300,
    100,
    50,
    30,
    20,
    10,
    5,
    0,
];

export const DEFAULT_MAP_CORNERS = {
    googleSearch: 0,
    lastTriggers: 0,
    layers: 1,
    hosts: 1,
    attribution: 3,
    scale: 3,
    measure: 3,
    myLocationButton: 2,
    zoom: 2,
};
