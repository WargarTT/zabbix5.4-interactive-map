<?php

use Imap\Components\Request;
bindtextdomain('imap', IMAP_MODULE_DIR . '/locale');
bind_textdomain_codeset('imap', 'UTF-8');

textdomain('imap');
$page['title'] = _('Interactive map');
textdomain('frontend');

$page['file'] = 'zabbix.php';
$page['hist_arg'] = array('groupid', 'hostid', 'show_severity', 'control_map', 'with_triggers_only');

$request = new Request();
$output = 'html';
$asset_url = IMAP_ASSET_URL;

$fields = array(
    'groupid' => array(T_ZBX_INT, O_OPT, P_SYS, DB_ID, null),
    'hostid' => array(T_ZBX_INT, O_OPT, P_SYS, DB_ID, null),
    'thostid' => array(T_ZBX_INT, O_OPT, P_SYS, DB_ID, null),
    'linkid' => array(T_ZBX_INT, O_OPT, P_SYS, DB_ID, null),
    'severity_min' => array(T_ZBX_INT, O_OPT, P_SYS, IN('0,1,2,3,4,5'), null),
    'fullscreen' => array(T_ZBX_INT, O_OPT, P_SYS, IN('0,1'), null),
    'control_map' => array(T_ZBX_INT, O_OPT, P_SYS, IN('0,1'), null),
    'with_triggers_only' => array(T_ZBX_INT, O_OPT, P_SYS, IN('0,1'), null),

    'output' => array(T_ZBX_STR, O_OPT, P_SYS, null, null),
    'jsscriptid' => array(T_ZBX_STR, O_OPT, P_SYS, null, null),
    // ajax
    'favobj' => array(T_ZBX_STR, O_OPT, P_ACT, null, null),
    'favref' => array(T_ZBX_STR, O_OPT, P_ACT, null, null),
    'favid' => array(T_ZBX_INT, O_OPT, P_ACT, null, null),
    'favcnt' => array(T_ZBX_INT, O_OPT, null, null, null),
    'pmasterid' => array(T_ZBX_STR, O_OPT, P_SYS, null, null),
    'favaction' => array(T_ZBX_STR, O_OPT, P_ACT, IN("'add','remove','refresh','flop','sort'"), null),
    'favstate' => array(T_ZBX_INT, O_OPT, P_ACT, NOT_EMPTY, 'isset({favaction})&&("flop"=={favaction})'),
    'favdata' => array(T_ZBX_STR, O_OPT, null, null, null),
    'hardwareField' => array(T_ZBX_STR, O_OPT, null, null, null),
    //стандартные
    'btnSelect' => [T_ZBX_STR, O_OPT, null, null, null],
    'filter_rst' => [T_ZBX_STR, O_OPT, P_SYS, null, null],
    'filter_set' => [T_ZBX_STR, O_OPT, P_SYS, null, null],
    'show_triggers' => [T_ZBX_INT, O_OPT, null, null, null],
    'show_events' => [T_ZBX_INT, O_OPT, P_SYS, null, null],
    'ack_status' => [T_ZBX_INT, O_OPT, P_SYS, null, null],
    'show_severity' => [T_ZBX_INT, O_OPT, P_SYS, null, null],
    'status_change_days' => [T_ZBX_INT, O_OPT, null, BETWEEN(1, DAY_IN_YEAR * 2), null],
    'status_change' => [T_ZBX_INT, O_OPT, null, null, null],
    'txt_select' => [T_ZBX_STR, O_OPT, null, null, null],
    'inventory' => [T_ZBX_STR, O_OPT, null, null, null],

    'r' => array(T_ZBX_STR, O_OPT, P_SYS, DB_ID, null),
    'query' => [T_ZBX_STR, O_OPT, P_SYS, null, null],
    'linkoptions' => [T_ARRAY, O_OPT, null, null, null],
);
check_fields($fields);

$pageFilter = (object) [
    'hostid' => (int) $request->get('hostid', 0),
    'groupid' => (int) $request->get('groupid', 0)
];


// filter set
if (hasRequest('filter_set')) {
    CProfile::update('web.tr_status.filter.show_details', getRequest('show_details', 0), PROFILE_TYPE_INT);
    CProfile::update('web.tr_status.filter.show_maintenance', getRequest('show_maintenance', 0), PROFILE_TYPE_INT);
    CProfile::update('web.tr_status.filter.show_severity',
        getRequest('show_severity', TRIGGER_SEVERITY_NOT_CLASSIFIED), PROFILE_TYPE_INT
    );
    CProfile::update('web.tr_status.filter.txt_select', getRequest('txt_select', ''), PROFILE_TYPE_STR);
    CProfile::update('web.tr_status.filter.status_change', getRequest('status_change', 0), PROFILE_TYPE_INT);
    CProfile::update('web.tr_status.filter.status_change_days', getRequest('status_change_days', 14),
        PROFILE_TYPE_INT
    );
    // show triggers
    // when this filter is set to "All" it must not be remembered in the profiles because it may render the
    // whole page inaccessible on large installations.
    if ((int)getRequest('show_triggers') !== TRIGGERS_OPTION_ALL) {
        CProfile::update('web.tr_status.filter.show_triggers', getRequest('show_triggers'), PROFILE_TYPE_INT);
    }

    // TODO: show events
    // $showEvents = getRequest('show_events', EVENTS_OPTION_NOEVENT);
//    if ($config['event_ack_enable'] == 0 || $showEvents != EVENTS_OPTION_NOT_ACK) {
//        CProfile::update('web.tr_status.filter.show_events', $showEvents, PROFILE_TYPE_INT);
//    }

    // TODO: ack status
//    if ($config['event_ack_enable'] == EVENT_ACK_ENABLED) {
//        CProfile::update('web.tr_status.filter.ack_status', getRequest('ack_status', ZBX_ACK_STS_ANY), PROFILE_TYPE_INT);
//    }

    // update host inventory filter
    $inventoryFields = [];
    $inventoryValues = [];
    foreach (getRequest('inventory', []) as $field) {
        if ($field['value'] === '') {
            continue;
        }

        $inventoryFields[] = $field['field'];
        $inventoryValues[] = $field['value'];
    }
    CProfile::updateArray('web.tr_status.filter.inventory.field', $inventoryFields, PROFILE_TYPE_STR);
    CProfile::updateArray('web.tr_status.filter.inventory.value', $inventoryValues, PROFILE_TYPE_STR);
} else if (hasRequest('filter_rst')) {
    DBStart();
    CProfile::delete('web.tr_status.filter.show_triggers');
    CProfile::delete('web.tr_status.filter.show_details');
    CProfile::delete('web.tr_status.filter.show_maintenance');
    CProfile::delete('web.tr_status.filter.show_events');
    CProfile::delete('web.tr_status.filter.ack_status');
    CProfile::delete('web.tr_status.filter.show_severity');
    CProfile::delete('web.tr_status.filter.txt_select');
    CProfile::delete('web.tr_status.filter.status_change');
    CProfile::delete('web.tr_status.filter.status_change_days');
    CProfile::deleteIdx('web.tr_status.filter.inventory.field');
    CProfile::deleteIdx('web.tr_status.filter.inventory.value');
    DBend();
}

if (hasRequest('filter_set') && (int)getRequest('show_triggers') === TRIGGERS_OPTION_ALL) {
    $showTriggers = TRIGGERS_OPTION_ALL;
} else {
    $showTriggers = CProfile::get('web.tr_status.filter.show_triggers', TRIGGERS_OPTION_RECENT_PROBLEM);
}

$showSeverity = CProfile::get('web.tr_status.filter.show_severity', TRIGGER_SEVERITY_NOT_CLASSIFIED);

if ($output !== 'block') {
    /*
    * Display
    */
    //$displayNodes = (is_show_all_nodes() && $pageFilter->groupid == 0 && $pageFilter->hostid == 0);

    // $showTriggers = $_REQUEST['show_triggers'];
    // $showEvents = $_REQUEST['show_events'];
    // $ackStatus = $_REQUEST['ack_status'];

// 	$triggerWidget = new CWidget();
// 
// 	$rightForm = new CForm('get');
// 	$rightForm->addItem(array(_('Group').SPACE, $pageFilter->getGroupsCB(true)));
// 	$rightForm->addItem(array(SPACE._('Host').SPACE, $pageFilter->getHostsCB(true)));
// 	$severityComboBox = new CComboBox('severity_min', $showSeverity,'javascript: submit();');
// 	$severityComboBox->addItems($pageFilter->severitiesMin);
// 	$rightForm->addItem(array(SPACE._('Minimum trigger severity').SPACE, $severityComboBox));
// 
// 	textdomain("imap");
// 	$rightForm->addItem(array(SPACE.SPACE._('Control map').SPACE, new CCheckBox('control_map', $control_map, '_imap.settings.do_map_control = jQuery(\'#control_map\')[0].checked; if (_imap.settings.do_map_control) {mapBbox(_imap.bbox)};', 1)));
// 	$rightForm->addItem(array(SPACE.SPACE._('With triggers only').SPACE, new CCheckBox('with_triggers_only', $with_triggers_only, 'javascript: submit();', 1)));
// 	textdomain("frontend");
// 	
// 	$rightForm->addVar('fullscreen', $_REQUEST['fullscreen']);
// 
// 	$triggerWidget->addHeader(SPACE,$rightForm);
// 	$triggerWidget->addPageHeader(_('Interactive map'), get_icon('fullscreen', array('fullscreen' => $_REQUEST['fullscreen'])));
// 		
// 	$triggerWidget->show();


    /*
    * Display
    */
    textdomain('imap');
    $triggerWidget = (new CWidget())
        ->setTitle(_('Interactive map'));

    if (isset($page['web_layout_mode'])) {
        $triggerWidget->setWebLayoutMode($page['web_layout_mode']);
    }

    $rightForm = (new CForm('get'))
        ->addVar('action', 'imap.view')
        ->addVar('fullscreen', $_REQUEST['fullscreen'] ?? 0);

    $triggerWidget->setControls($rightForm);

    $triggerWidget->show();

}

$version = trim(file_get_contents(IMAP_MODULE_DIR . '/imap/version'));

textdomain('imap');

//проверяем наличие таблиц в БД
$check_links = true;
if (!DBselect('SELECT 1 FROM hosts_links')) {
    $check_links = false;
    clear_messages(1);
}

if (!DBselect('SELECT 1 FROM hosts_links_settings')) {
    $check_links = false;
    clear_messages(1);
}

//проверяем наличие функции json_encode
if (!function_exists('json_encode')) {
    error("No function 'json_encode' in PHP. Look this http://stackoverflow.com/questions/18239405/php-fatal-error-call-to-undefined-function-json-decode");
}

//проверяем доступ к файлам скрипта
$needThisFiles = array(
    IMAP_MODULE_DIR . '/imap/leaflet/leaflet.js',
    IMAP_MODULE_DIR . '/imap/leaflet/plugins/markercluster/leaflet.markercluster.js',
    IMAP_MODULE_DIR . '/imap/imap.js'
);
foreach ($needThisFiles as $file) {
    if (!is_readable($file)) {
        error(_('If you see this message, it means that the script had problems with access to the files. Try to set read permissions for the web-server to a folder imap.'));
        break;
    }
}

?>

    <div id="imapworkarea" style="display:none; position:relative;">
        <div id="mapdiv" style="width:100%; height:300px;"></div>
        <div id="ajax"></div>
        <div id="imapmes">
        </div>
    </div>


    <link rel="stylesheet" href="<?php echo $asset_url; ?>/leaflet/leaflet.css"/>
    <script type="text/javascript" src="<?php echo $asset_url; ?>/leaflet/leaflet.js"></script>
    <link rel="stylesheet" href="<?php echo $asset_url; ?>/leaflet/plugins/markercluster/MarkerCluster.css"/>
    <link rel="stylesheet" href="<?php echo $asset_url; ?>/leaflet/plugins/markercluster/MarkerCluster.Default.css"/>
    <script src="<?php echo $asset_url; ?>/leaflet/plugins/markercluster/leaflet.markercluster.js"></script>

<?php

/*
 * TODO: external layers
     <!--<script src="<?php echo $asset_url; ?>/leaflet/plugins/layer/tile/Bing.js"></script>-->

    <!--<script src="https://api-maps.yandex.ru/2.1/?load=package.map&lang=<?php echo CWebUser::$data['lang']; ?>" type="text/javascript"></script>
    <script src="<?php echo $asset_url; ?>/leaflet/plugins/layer/tile/Yandex.js"></script>-->

    // GOOGLE
    <script src="<?php echo $asset_url; ?>/leaflet/plugins/layer/tile/Google.js"></script>

 */
?>

    <script src="<?php echo $asset_url; ?>/leaflet/ext-plugins/jquery.fs.stepper.min.js"></script>
    <link rel="stylesheet" href="<?php echo $asset_url; ?>/leaflet/ext-plugins/jquery.fs.stepper.css"/>

    <script type="text/javascript" src="<?php echo $asset_url; ?>/leaflet/ext-plugins/colorpicker/colors.js"></script>
    <script type="text/javascript" src="<?php echo $asset_url; ?>/leaflet/ext-plugins/colorpicker/jqColorPicker.js"></script>

    <link rel="stylesheet" href="<?php echo $asset_url; ?>/leaflet/plugins/zoomslider/L.Control.Zoomslider.css"/>
    <script src="<?php echo $asset_url; ?>/leaflet/plugins/zoomslider/L.Control.Zoomslider.js"></script>

    <script src="<?php echo $asset_url; ?>/leaflet/plugins/leaflet.measure/leaflet.measure.js"></script>
    <link rel="stylesheet" href="<?php echo $asset_url; ?>/leaflet/plugins/leaflet.measure/leaflet.measure.css"/>

    <link rel="stylesheet" href="<?php echo $asset_url; ?>/markers.css?<?php echo mt_rand(); ?>"/>

<?php
if (file_exists(IMAP_MODULE_DIR . '/imap/userstyles.css')) {
    /** @noinspection HtmlUnknownTarget */
    echo '<link rel="stylesheet" href="' . $asset_url . '/userstyles.css" />';
}
?>


    <script type="text/javascript">

        window._imap = {};

        _imap.settings = {};
        _imap.settings.lang = "<?php echo CWebUser::$data['lang']; ?>";


        /* This settings changing in interactive mode */
        _imap.settings.doMapControl = false;
        _imap.settings.pauseMapControl = false;
        _imap.settings.showWithTriggersOnly = false;
        _imap.settings.minStatus = <?php echo TRIGGER_SEVERITY_HIGH; ?>;
        _imap.mapCorners = {};
        _imap.version = '<?php echo $version; ?>';
        _imap.zabbixVersion = '<?php echo ZABBIX_VERSION; ?>';

        /* This settings changing in file settings.js */
        _imap.settings.showIcons = true;
        _imap.settings.useSearch = true;
        _imap.settings.useZoomSlider = true;
        _imap.settings.linksEnabled = true;
        _imap.settings.debugEnabled = true;
        _imap.settings.hardwareField = 'type';
        _imap.settings.maxMarkersSpiderfy = 50;
        _imap.settings.excludingInventory = ['hostid', 'location_lat', 'location_lon', 'url_a', 'url_b', 'url_c'];
        _imap.settings.useIconsInMarkers = true;
        _imap.settings.startCoordinates = [59.95, 30.29];
        _imap.settings.startZoom = 4;
        _imap.settings.mapAnimation = true;
        _imap.settings.hostUpdateInterval = 60;
        _imap.settings.triggerUpdateInterval = 30;
        _imap.settings.intervalLoadLinks = 60;
        _imap.settings.showMarkersLabels = false;
        _imap.settings.spiderfyDistanceMultiplier = 1;
        _imap.settings.defaultBaseLayer = "OpenStreetMap";
        _imap.settings.startBaseLayer = "OpenStreetMap";
        _imap.settings.weatherApiKey = "-";
        _imap.settings.bingApiKey = false;
        _imap.settings.staffApi = {
            enable: false,
            url: null,
            refreshInterval: 60,
            authHeaders: null
        };

        _imap.mapCorners.googleSearch = 0;
        _imap.mapCorners.lasttriggers = 0;
        _imap.mapCorners.layers = 1;
        _imap.mapCorners.hosts = 1;
        _imap.mapCorners.attribution = 3;
        _imap.mapCorners.scale = 3;
        _imap.mapCorners.measure = 3;
        _imap.mapCorners.mylocationbutton = 2;
        _imap.mapCorners.zoom = 2;


        locale = locale || {};

        /* Перевод для текущего языка */
        <?php textdomain('frontend'); ?>
        locale.Search = '<?php echo _('Search'); ?>';

        locale.inventoryfields = {};
        <?php foreach (getHostInventories() as $field): ?>
        locale.inventoryfields["<?php echo $field['db_field'] ?>"] = "<?php echo $field['title'] ?>";
        <?php endforeach; ?>

        locale['Ack'] = '<?php echo _('Ack'); ?>';
        locale['Yes'] = '<?php echo _('Yes'); ?>';
        locale['No'] = '<?php echo _('No'); ?>';

        locale['Host inventory'] = '<?php echo _('Host inventory'); ?>';
        locale['Triggers'] = '<?php echo _('Triggers'); ?>';
        locale['Graphs'] = '<?php echo _('Graphs'); ?>';
        locale['Latest data'] = '<?php echo _('Latest data'); ?>';
        locale['Host'] = '<?php echo _('Host'); ?>';
        locale['Items'] = '<?php echo _('Items'); ?>';
        locale['Discovery rules'] = '<?php echo _('Discovery rules'); ?>';
        locale['Web scenarios'] = '<?php echo _('Web scenarios'); ?>';

        <?php textdomain('imap'); ?>
        locale['Change location'] = '<?php echo _('Change location'); ?>';
        locale['Delete location'] = '<?php echo _('Delete location'); ?>';
        locale['Add a link to another host'] = '<?php echo _('Add a link to another host'); ?>';
        locale['Select a new position'] = '<?php echo _('Select a new position'); ?>';
        locale['Failed to update data'] = '<?php echo _('Failed to update data'); ?>';
        locale['Failed to get data'] = '<?php echo _('Failed to get data'); ?>';
        locale['Error'] = '<?php echo _('Error'); ?>';
        locale['Hosts'] = '<?php echo _('Hosts'); ?>';
        locale['This host does not have coordinates'] = '<?php echo _('This host does not have coordinates'); ?>';
        locale['Set a hardware type'] = '<?php echo _('Set a hardware type'); ?>';
        locale['Hardware type updated'] = '<?php echo _('Hardware type updated'); ?>';
        locale["Host's links"] = "<?php echo _("Host\'s links"); ?>";
        locale['Show debug information'] = "<?php echo _('Show debug information'); ?>";
        locale['Debug information'] = "<?php echo _('Debug information'); ?>";
        locale['Select hosts for links'] = "<?php echo _('Select hosts for links'); ?>";
        locale['Name'] = "<?php echo _('Name'); ?>";
        locale['Delete link'] = "<?php echo _('Delete link'); ?>";
        locale['Link options'] = "<?php echo _('Link options'); ?>";
        locale['Link name'] = "<?php echo _('Link name'); ?>";
        locale['Link color'] = "<?php echo _('Link color'); ?>";
        locale['Link width'] = "<?php echo _('Link width'); ?>";
        locale['Link opacity'] = "<?php echo _('Link opacity'); ?>";
        locale['Link dash'] = "<?php echo _('Link dash'); ?>";
        locale['Delete confirm'] = "<?php echo _('Delete confirm'); ?>";
        locale['Successful'] = "<?php echo _('Successful'); ?>";
        locale['Zoom in'] = "<?php echo _('Zoom in'); ?>";
        locale['Zoom out'] = "<?php echo _('Zoom out'); ?>";
        locale['No hosts with inventory'] = "<?php echo _('No hosts with inventory'); ?>";
        locale['Keep'] = "<?php echo _('Keep'); ?>";
        locale['Tools'] = "<?php echo _('Tools'); ?>";
        locale['Sort by severity'] = "<?php echo _('Sort by severity'); ?>";
        locale['Sort by time'] = "<?php echo _('Sort by time'); ?>";
        locale['Config'] = '<?php echo _('Config'); ?>';
        locale['Host config'] = '<?php echo _('Host config'); ?>';
        locale['Host view'] = '<?php echo _('Host view'); ?>';
        locale['Wind speed'] = "<?php echo _('Wind speed'); ?>";
        locale['Wind points'] = "<?php echo _('Wind points'); ?>";
        locale['Wind type'] = "<?php echo _('Wind type'); ?>";
        locale['Wind direction'] = "<?php echo _('Wind direction'); ?>";
        locale['Temperature'] = "<?php echo _('Temperature'); ?>";
        locale['Humidity'] = "<?php echo _('Humidity'); ?>";
        locale['Pressure'] = "<?php echo _('Pressure'); ?>";
        locale['Sunset'] = "<?php echo _('Sunset'); ?>";
        locale['Sunrise'] = "<?php echo _('Sunrise'); ?>";
        locale['Data obtained'] = "<?php echo _('Data obtained'); ?>";
        locale['Show weather'] = "<?php echo _('Show weather'); ?>";

        /* Фильтр для отбора хостов и групп */
        _imap.filter = {
            showSeverity: 0,
            hostId: <?php echo $pageFilter->hostid; ?>,
            groupId: <?php echo $pageFilter->groupid; ?>
        };
    </script>


<?php
if (file_exists(IMAP_MODULE_DIR . '/imap/settings.js')) {
    echo '<script src="' . $asset_url . '/settings.js?' . mt_rand() . '"></script>';
}

?>

    <script type="text/javascript" src="<?php echo $asset_url; ?>/dist/build.js?<?php echo mt_rand(); ?>"></script>


    <script type="text/javascript" src="<?php echo $asset_url; ?>/imap.js<?php echo '?' . mt_rand(); ?>"></script>

<?php


if (file_exists(IMAP_MODULE_DIR . '/imap/additions.js')) {
    echo '<script src="' . $asset_url . '/additions.js?' . mt_rand() . '"></script>';
}
if (!$check_links) {
    echo '<script type="text/javascript"> _imap.settings.links_enabled = false; </script>';
}


echo '<script type="text/javascript" src="' . $asset_url . '/thirdtools.js"></script>';

textdomain('frontend');
