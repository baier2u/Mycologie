<?php

namespace App\Middlewares;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Utils\Session;

/**
 * AdminMiddleware.php
 *
 * Manage administrator user
 *
 * @package    Middlewares
 * @author     WILMOUTH Steven
 * @version    1
 */
class AdminMiddleware
{

    private $container;

    /**
     * AdminMiddleware constructor.
     * @param \Twig_Environment $twig
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Invoke middleware
     * @param RequestInterface $request
     * @param RequestInterface $response
     * @param $next
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, $next)
    {
        if (Session::isLogged('user')) {
            if (Session::get('user')->role == 'admin') {
                return $next($request, $response);
            } else {
                return $response->withStatus(302)->withHeader('Location', $this->container->get('router')->pathFor('index', []));
            }
        } else {
            return $response->withStatus(302)->withHeader('Location', $this->container->get('router')->pathFor('index', []));
        }
    }
    
}
