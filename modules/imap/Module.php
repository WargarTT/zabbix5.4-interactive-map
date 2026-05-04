<?php

namespace Modules\Imap;

use APP;
use CMenuItem;

class Module extends \Core\CModule {
    public function init(): void {
        APP::Component()->get('menu.main')
            ->findOrAdd(_('Monitoring'))
            ->getSubmenu()
            ->add((new CMenuItem(_('Interactive map')))->setAction('imap.view'));
    }
}
