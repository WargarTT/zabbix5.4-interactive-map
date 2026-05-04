<?php

namespace Modules\Imap\Actions;

use CController;
use CControllerResponseData;

class MapView extends CController {
    public function init(): void {
        $this->disableSIDvalidation();
    }

    protected function checkInput(): bool {
        return true;
    }

    protected function checkPermissions(): bool {
        return $this->getUserType() >= USER_TYPE_ZABBIX_USER;
    }

    protected function doAction(): void {
        $this->setResponse(new CControllerResponseData([
            'title' => _('Interactive map')
        ]));
    }
}
