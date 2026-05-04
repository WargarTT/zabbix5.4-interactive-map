<?php

namespace Modules\Imap\Actions;

use CController;
use Imap\Components\Request;
use Imap\Components\Route;
use Imap\Components\Router;
use Imap\Controllers\Ajax\GraphController;
use Imap\Controllers\Ajax\HardwareController;
use Imap\Controllers\Ajax\HostController;
use Imap\Controllers\Ajax\LinkController;
use Imap\Controllers\Ajax\TriggerController;
use Modules\Imap\Bootstrap;

require_once __DIR__ . '/../Bootstrap.php';

class MapAjax extends CController {
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
        Bootstrap::init();

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=UTF-8');
        }

        $request = new Request();
        $router = (new Router($request))
            ->addRoute(new Route('ajax/hosts/view', HostController::class, 'view'))
            ->addRoute(new Route('ajax/hosts/update-location', HostController::class, 'updateLocation'))
            ->addRoute(new Route('ajax/hosts/search', HostController::class, 'search'))
            ->addRoute(new Route('ajax/hosts', HostController::class, 'index'))
            ->addRoute(new Route('ajax/triggers', TriggerController::class, 'index'))
            ->addRoute(new Route('ajax/links/view', LinkController::class, 'view'))
            ->addRoute(new Route('ajax/links/update', LinkController::class, 'update'))
            ->addRoute(new Route('ajax/links/delete', LinkController::class, 'delete'))
            ->addRoute(new Route('ajax/links/create', LinkController::class, 'create'))
            ->addRoute(new Route('ajax/links', LinkController::class, 'index'))
            ->addRoute(new Route('ajax/graph', GraphController::class, 'index'))
            ->addRoute(new Route('ajax/hardware', HardwareController::class, 'index'))
            ->addRoute(new Route('ajax/hardware/update', HardwareController::class, 'update'));

        if (!$router->handleRoute()) {
            echo json_encode([
                'jsonrpc' => '2.0',
                'error' => ['message' => 'Route not found.']
            ]);
        }

        exit;
    }
}
