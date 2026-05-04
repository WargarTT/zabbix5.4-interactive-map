<?php

namespace Modules\Imap;

final class Bootstrap {
    private static $initialized = false;

    public static function init(): void {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        if (!defined('ZBX_ACK_STS_ANY')) {
            define('ZBX_ACK_STS_ANY', 1);
        }

        if (!defined('IMAP_MODULE_DIR')) {
            define('IMAP_MODULE_DIR', __DIR__);
        }

        if (!defined('IMAP_ASSET_URL')) {
            define('IMAP_ASSET_URL', 'modules/imap/imap');
        }

        bindtextdomain('imap', __DIR__ . '/locale');
        bind_textdomain_codeset('imap', 'UTF-8');

        require_once __DIR__ . '/imap/DB.php';

        spl_autoload_register(static function ($class): void {
            if (preg_match('/^Imap\\\\(.*)$/i', $class, $matches)) {
                $file_path = __DIR__ . '/imap/' . str_replace('\\', DIRECTORY_SEPARATOR, $matches[1]) . '.php';

                if (file_exists($file_path)) {
                    require_once $file_path;
                }
            }
        });
    }
}
