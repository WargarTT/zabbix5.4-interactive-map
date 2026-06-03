<?php


namespace Imap\Controllers\Ajax;

use API;

/**
 * Class TriggerController
 * @package Imap\Controllers\Ajax
 */
class TriggerController extends BaseAjaxController
{
    /**
     * @return array|int
     */
    public function actionIndex()
    {
        $options = $this->getBaseOptions();

        $problem_options = [
            'output' => ['eventid', 'objectid', 'clock', 'name', 'severity', 'acknowledged'],
            'source' => defined('EVENT_SOURCE_TRIGGERS') ? EVENT_SOURCE_TRIGGERS : 0,
            'object' => defined('EVENT_OBJECT_TRIGGER') ? EVENT_OBJECT_TRIGGER : 0,
            'recent' => false,
            'severities' => [TRIGGER_SEVERITY_HIGH, TRIGGER_SEVERITY_DISASTER],
            'sortfield' => ['clock'],
            'sortorder' => 'DESC',
            'preservekeys' => true
        ];

        if (array_key_exists('hostids', $options)) {
            $problem_options['hostids'] = $options['hostids'];
        }

        if (array_key_exists('groupids', $options)) {
            $problem_options['groupids'] = $options['groupids'];
        }

        $problems = API::Problem()->get($problem_options);
        if (!is_array($problems)) {
            $problems = [];
        }

        $triggerids = [];
        $last_events = [];

        foreach ($problems as $problem) {
            if (!is_array($problem)) {
                continue;
            }

            if (!array_key_exists('objectid', $problem)) {
                continue;
            }

            $triggerid = $problem['objectid'];
            $triggerids[$triggerid] = $triggerid;

            if (!array_key_exists($triggerid, $last_events)) {
                $problem['source'] = $problem_options['source'];
                $problem['object'] = $problem_options['object'];
                $problem['value'] = TRIGGER_VALUE_TRUE;
                $last_events[$triggerid] = $problem;
            }
        }

        $fallback_options = $options;
        $fallback_options['output'] = ['triggerid', 'description', 'status', 'value', 'priority', 'lastchange'];
        $fallback_options['expandData'] = true;
        $fallback_options['expandDescription'] = true;
        $fallback_options['selectLastEvent'] = 'extend';
        $fallback_options['sortfield'] = ['lastchange'];
        $fallback_options['sortorder'] = 'DESC';
        $fallback_options['filter'] = [
            'value' => TRIGGER_VALUE_TRUE,
            'status' => defined('TRIGGER_STATUS_ENABLED') ? TRIGGER_STATUS_ENABLED : 0
        ];
        $fallback_options['min_severity'] = TRIGGER_SEVERITY_HIGH;
        $fallback_options['selectHosts'] = ['hostid', 'name'];
        $fallback_options['preservekeys'] = true;

        $fallback_triggers = API::Trigger()->get($fallback_options);
        if (!is_array($fallback_triggers)) {
            $fallback_triggers = [];
        }

        foreach ($fallback_triggers as $triggerid => $trigger) {
            if (!is_array($trigger)) {
                continue;
            }

            if (array_key_exists($triggerid, $triggerids)) {
                continue;
            }

            if (!array_key_exists('lastEvent', $trigger) || !is_array($trigger['lastEvent']) || !$trigger['lastEvent']) {
                continue;
            }

            if (array_key_exists('value', $trigger['lastEvent']) && $trigger['lastEvent']['value'] != TRIGGER_VALUE_TRUE) {
                continue;
            }

            if (array_key_exists('r_eventid', $trigger['lastEvent']) && $trigger['lastEvent']['r_eventid'] != 0) {
                continue;
            }

            $triggerids[$triggerid] = $triggerid;
            $last_events[$triggerid] = $trigger['lastEvent'];
        }

        if (!$triggerids) {
            return [];
        }

        $trigger_options = $options;
        $trigger_options['triggerids'] = array_values($triggerids);
        $trigger_options['output'] = ['triggerid', 'description', 'status', 'priority', 'lastchange'];
        $trigger_options['expandData'] = true;
        $trigger_options['expandDescription'] = true;
        $trigger_options['selectHosts'] = ['hostid', 'name'];
        $trigger_options['preservekeys'] = true;
        unset($trigger_options['hostids'], $trigger_options['groupids']);

        $triggers = API::Trigger()->get($trigger_options);
        if (!is_array($triggers)) {
            return [];
        }

        foreach ($triggers as $triggerid => &$trigger) {
            if (!array_key_exists($triggerid, $last_events)) {
                continue;
            }

            $trigger['value'] = TRIGGER_VALUE_TRUE;
            $trigger['lastEvent'] = $last_events[$triggerid];
        }
        unset($trigger);

        return $triggers;
    }
}
