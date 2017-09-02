<?php
namespace Shaarli\Ajax;

use Shaarli\Config\ConfigManager;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class ApiMiddleware
 *
 * This will be called before accessing any API Controller.
 * Its role is to make sure that the API is enabled, configured, and to validate the JWT token.
 *
 * If the request is validated, the controller is called, otherwise a JSON error response is returned.
 *
 */
class AjaxMiddleware
{
    /**
     * @var Container: contains conf, plugins, etc.
     */
    protected $container;

    /**
     * @var ConfigManager instance.
     */
    protected $conf;

    /**
     * ApiMiddleware constructor.
     *
     * @param Container $container instance.
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Middleware execution:
     *   - check the API request
     *   - execute the controller
     *   - return the response
     *
     * @param  Request  $request  Slim request
     * @param  Response $response Slim response
     * @param  callable $next     Next action
     *
     * @return Response response.
     */
    public function __invoke($request, $response, $next)
    {
        return $next($request, $response);
    }
}
