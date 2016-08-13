<?php

/**
 * Class LinksPerPageControllerTest
 *
 * Test the LinksPerPage controller.
 */
class LinksPerPageControllerTest extends ControllerTest
{
    /**
     * Initialize the controller.
     */
    public function setUp()
    {
        parent::setUp();
        $this->controller = new LinksPerPageController();
        $this->controller->setTpl($this->pageBuilder);
        $this->controller->setConf($this->conf);
        $this->controller->setPluginManager($this->pluginManager);
        $this->controller->setLinkDB($this->linkDB);
    }

    /**
     * Test redirect, and make sure that given value has been process.
     *
     * @runInSeparateProcess
     */
    public function testLinksPerPageValid()
    {
        $server = array(
            'HTTP_REFERER' => '',
            'HTTP_HOST' => '',
        );
        $this->controller->setServer($server);
        $value = 33;
        $this->controller->setGet(array('linksperpage' => $value));
        $this->assertTrue($this->controller->redirect());
        $session = $this->controller->getSession();
        $this->assertEquals($value, $session['LINKS_PER_PAGE']);
        $headers = xdebug_get_headers();
        $this->assertTrue($this->isHeaderSetRegex('Location: \?$', $headers));

        // redirect
        $server = array(
            'HTTP_REFERER' => 'http://test.tld/somewhere',
            'HTTP_HOST' => 'test.tld',
        );
        $this->controller->setServer($server);
        $value = '65';
        $this->controller->setGet(array('linksperpage' => $value));
        $this->assertTrue($this->controller->redirect());
        $session = $this->controller->getSession();
        $this->assertEquals($value, $session['LINKS_PER_PAGE']);
        $headers = xdebug_get_headers();
        $this->assertTrue($this->arrayContains('Location: '. $server['HTTP_REFERER'], $headers));
    }

    /**
     * Try to set invalid value.
     *
     * @runInSeparateProcess
     */
    public function testLinksPerPageInvalid()
    {
        $server = array(
            'HTTP_REFERER' => '',
            'HTTP_HOST' => '',
        );
        $this->controller->setServer($server);
        $value = 0;
        $this->controller->setGet(array('linksperpage' => $value));
        $this->assertTrue($this->controller->redirect());
        $session = $this->controller->getSession();
        $this->assertEmpty($session['LINKS_PER_PAGE']);
        $headers = xdebug_get_headers();
        $this->assertTrue($this->isHeaderSetRegex('Location: \?$', $headers));

        $value = 'minimoys';
        $this->controller->setGet(array('linksperpage' => $value));
        $this->assertTrue($this->controller->redirect());
        $session = $this->controller->getSession();
        $this->assertEmpty($session['LINKS_PER_PAGE']);
        $headers = xdebug_get_headers();
        $this->assertTrue($this->isHeaderSetRegex('Location: \?$', $headers));

        $value = '';
        $this->controller->setGet(array('linksperpage' => $value));
        $this->assertTrue($this->controller->redirect());
        $session = $this->controller->getSession();
        $this->assertEmpty($session['LINKS_PER_PAGE']);
        $headers = xdebug_get_headers();
        $this->assertTrue($this->isHeaderSetRegex('Location: \?$', $headers));
    }

    /**
     * Test render(), should be null.
     */
    public function testRender()
    {
        $this->assertNull($this->controller->render());
    }
}
