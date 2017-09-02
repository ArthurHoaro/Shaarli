<?php

namespace Shaarli\Ajax;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class Tags
{
    /**
     * @var Container
     */
    protected $ci;

    /**
     * Tags constructor.
     *
     * @param Container $ci
     */
    public function __construct($ci)
    {
        $this->ci = $ci;
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return Response array containing all available tags.
     */
    public function getAll($request, $response)
    {
        /** @var \LinkDB $linkDB */
        $linkDB = $this->ci->get('db');
        $tags = $linkDB->linksCountPerTag();

        return $response->withJson(array_keys($tags), 200);
    }
}
