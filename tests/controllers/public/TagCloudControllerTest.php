<?php

/**
 * Class TagCloudControllerTest
 *
 * Test tag cloud rendering.
 */
class TagCloudControllerTest extends ControllerTest
{
    /**
     * Initialize the controller.
     */
    public function setUp()
    {
        parent::setUp();
        $this->controller = new TagCloudController();
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
     * Test on render(): check that the right template is rendered, a basic sort test.
     */
    public function testRender()
    {
        $this->expectOutputRegex(
            '#id="cloudtag".*'.
            '<span class="count">2</span><a[^>]+>dev</a>.*'.
            '<span class="count">1</span><a[^>]+>media</a>#msi'
        );
        $this->controller->render();
    }
}