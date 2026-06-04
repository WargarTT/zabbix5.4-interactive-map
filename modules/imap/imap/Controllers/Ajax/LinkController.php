<?php

/** @noinspection PhpUnused */


namespace Imap\Controllers\Ajax;

use DBimap;

/**
 * Class LinkController
 * @package Imap\Controllers\Ajax
 */
class LinkController extends BaseAjaxController
{
    /**
     * @return array
     */
    public function actionIndex(): array
    {
        $hostsLinks = DBimap::find('hosts_links');
        $hostsLinksSettings = DBimap::find('hosts_links_settings');

        return $this->prepareLinkData($hostsLinks, $hostsLinksSettings);
    }

    /**
     * @param $hostLinks
     * @param $hostsLinksSettings
     * @return array
     */
    private function prepareLinkData($hostLinks, $hostsLinksSettings): array
    {
        $res = [];

        if (!is_array($hostLinks)) {
            $hostLinks = [];
        }

        if (!is_array($hostsLinksSettings)) {
            $hostsLinksSettings = [];
        }

        foreach ($hostLinks as $record) {
            if (!is_array($record)) {
                continue;
            }

            foreach ($hostsLinksSettings as $res2t) {
                if (!is_array($res2t)) {
                    continue;
                }

                if (array_key_exists('id', $record)
                        && array_key_exists('ids', $res2t)
                        && $record['id'] === $res2t['ids']) {
                    $record += $res2t;
                }
            }

            if (!array_key_exists('dash', $record) || !$record['dash']) {
                $record['dash'] = null;
            }

            if (!array_key_exists('weight', $record) || !$record['weight']) {
                $record['weight'] = null;
            }

            if (!array_key_exists('color', $record) || !$record['color']) {
                $record['color'] = null;
            }

            if (!array_key_exists('opacity', $record) || !$record['opacity']) {
                $record['opacity'] = null;
            }

            $res[] = $record;
        }

        return $res;
    }

    /**
     * @return array
     */
    public function actionCreate(): array
    {
        $options = $this->getBaseOptions();
        $hostId = $options['hostids'];
        if (!$this->checkHostsIsWritable($hostId)) {
            return $this->accessError();
        }

        $startHostId = $hostId;
        $thostId = $this->request->getBodyParam('thostId', []);

        $newLinks = [];
        foreach ($thostId as $targetHost) {
            if ($this->checkHostsIsWritable([$targetHost])) {
                $newLink = ['host1' => min($startHostId, $targetHost), 'host2' => MAX($startHostId, $targetHost)];
                $result = DBimap::find('hosts_links', $newLink);
                if (empty($result)) {
                    DBimap::insert('hosts_links', [$newLink]);
                    $result = DBimap::find('hosts_links', $newLink);
                }

                foreach ($result as $link) {
                    if(!in_array($link['id'], $newLinks, true)) {
                        $newLinks[] = $link['id'];
                    }
                }
            }
        }

        return $this->getLinkData($newLinks);
    }

    /**
     * @return array
     */
    public function actionView(): array
    {
        return $this->getLinkData($this->getLinkId());
    }

    /**
     * @param $linkId
     * @return array
     */
    protected function getLinkData($linkId): array
    {
        $hostsLinks = DBimap::find('hosts_links', ['id' => $linkId]);
        $hostsLinksSettings = DBimap::find('hosts_links_settings', ['ids' => $linkId]);

        return $this->prepareLinkData($hostsLinks, $hostsLinksSettings);
    }

    /**
     * @return int
     */
    private function getLinkId(): int
    {
        return (int)$this->request->get('linkid');
    }

    /**
     * @return array
     */
    public function actionDelete(): array
    {
        $linkId = $this->getLinkId();
        if (!$this->checkAccess($linkId)) {
            return $this->accessError();
        }

        DBimap::delete('hosts_links_settings', ['ids' => [$linkId]]);
        DBimap::delete('hosts_links', ['id' => [$linkId]]);

        return [
            'result' => TRUE
        ];
    }

    /**
     * @param int $linkId
     * @return bool
     */
    protected function checkAccess(int $linkId): bool
    {
        $glinks = DBfetchArray(DBselect(
            'SELECT host1, host2 FROM hosts_links WHERE hosts_links.id = ' . $linkId
        ));

        return $this->checkHostsIsWritable([1 * $glinks[0]['host1'], 1 * $glinks[0]['host2']]);
    }

    /**
     * @return array
     */
    public function actionUpdate(): array
    {
        $linkId = $this->getLinkId();

        if (!$this->checkAccess($linkId)) {
            return $this->accessError();
        }

        $linkOptions = $this->request->getBodyParam('linkoptions', []);
        if (!is_array($linkOptions)) {
            $linkOptions = [];
        }

        $newLink = ['values' => ['name' => $linkOptions['name'] ?? ''], 'where' => ['id' => $linkId]];

        DBimap::update('hosts_links', [$newLink]);
        DBimap::delete('hosts_links_settings', ['ids' => [$linkId]]);
        DBimap::insert('hosts_links_settings', [[
            'ids' => $linkId,
            'color' => $linkOptions['color'] ?? null,
            'weight' => $linkOptions['weight'] ?? null,
            'opacity' => $linkOptions['opacity'] ?? null,
        ]
        ]);

        return $this->getLinkData($linkId);
    }


}
