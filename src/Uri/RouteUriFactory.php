<?php
/**
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */
namespace GpsLab\Component\Sitemap\Uri;

use Symfony\Component\Routing\RouterInterface;

class RouteUriFactory
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var string
     */
    protected $url_class = '';

    /**
     * @param RouterInterface $router
     * @param string $url_class
     */
    public function __construct(RouterInterface $router, $url_class)
    {
        $this->router = $router;
        $this->url_class = $url_class;
    }

    /**
     * @param string $name
     * @param array $parameters
     *
     * @return UriInterface
     */
    public function create($name, array $parameters = [])
    {
        $class_name = $this->url_class;
        /* @var $url UriInterface */
        return new $class_name($this->router->generate($name, $parameters, RouterInterface::ABSOLUTE_URL));
    }
}
