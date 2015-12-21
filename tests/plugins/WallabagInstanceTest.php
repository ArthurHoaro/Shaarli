<?php

/**
 * Class WallabagInstanceTest
 */

require_once 'plugins/wallabag/WallabagInstance.php';

class WallabagInstanceTest extends PHPUnit_Framework_TestCase
{
    private $instance;
    /**
     * Reset plugin path
     */
    function setUp()
    {
        $this->instance = 'http://some.url';
    }

    function testWallabagInstanceV1() {
        $instance = new WallabagInstance($this->instance, 1);
    }

    function testWallabagInstanceV2() {
        $instance = new WallabagInstance($this->instance, 2);
    }
    /**
     *
     */
    function testWallabagInstanceInvalidVersion() {
        $instance = new WallabagInstance($this->instance, false);
        $instance = new WallabagInstance($this->instance, 3);
    }
}