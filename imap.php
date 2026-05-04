<?php

require_once __DIR__ . '/include/config.inc.php';

$query = $_GET;
unset($query['action']);
$query = http_build_query(array_merge(['action' => 'imap.view'], $query));

redirect('zabbix.php?' . $query);
