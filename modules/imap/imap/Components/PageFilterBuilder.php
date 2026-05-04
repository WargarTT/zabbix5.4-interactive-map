<?php


namespace Imap\Components;

/**
 * Class PageFilterBuilder
 * @package Imap\Components
 */
class PageFilterBuilder
{
    private static $instance;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var object
     */
    private $filter;

    /**
     * PageFilterBuilder constructor.
     * @param Request $request
     */
    private function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param Request $request
     * @return PageFilterBuilder
     */
    public static function getInstance(Request $request): PageFilterBuilder
    {
        if (static::$instance === null) {
            static::$instance = new PageFilterBuilder($request);
        }

        return static::$instance;
    }

    /**
     * @return object
     */
    public function getFilter()
    {
        if ($this->filter === null) {
            $this->filter = (object) [
                'hostid' => (int) $this->request->get('hostid', 0),
                'groupid' => (int) $this->request->get('groupid', 0),
                'hostsSelected' => true
            ];
        }

        return $this->filter;
    }

}
