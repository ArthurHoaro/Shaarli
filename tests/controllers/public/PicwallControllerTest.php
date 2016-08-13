<?php

/**
 * Class PicwallControllerTest
 *
 * Test the picwall rendering.
 */
class PicwallControllerTest extends ControllerTest
{
    /**
     * Initialize the controller.
     */
    public function setUp()
    {
        parent::setUp();
        $this->controller = new PicwallController();
        $this->controller->setTpl($this->pageBuilder);
        $this->controller->setConf($this->conf);
        $this->controller->setPluginManager($this->pluginManager);
        $this->controller->setLinkDB($this->linkDB);
    }

    /**
     * Test redirect(): shouldn't trigger any redirection.
     */
    public function testRedirect()
    {
        $this->assertFalse($this->controller->redirect());
    }

    /**
     * Test on render(): Not testable.
     */
    public function testRender()
    {
        $this->markTestIncomplete('Thumbnails need to be refactored.');
        $this->expectOutputRegex('/id="picwall_container/');
        $this->controller->render();
    }
}