<?php
/**
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */
namespace GpsLab\Component\Sitemap\Uri;

class UriFactory
{
    /**
     * @var string
     */
    protected $url_class = '';

    /**
     * @param string $url_class
     */
    public function __construct($url_class)
    {
        $this->url_class = $url_class;
    }

    /**
     * @param string $loc
     *
     * @return UriInterface
     */
    public function create($loc)
    {
        $class_name = $this->url_class;
        /* @var $url UriInterface */
        return new $class_name($loc);
    }
}
