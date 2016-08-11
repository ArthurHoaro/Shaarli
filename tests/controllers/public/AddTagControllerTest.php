<?php

/**
 * Class AddTagControllerTest
 */
class AddTagControllerTest extends ControllerTest
{
    /**
     * Initialize the controller.
     */
    public function setUp()
    {
        parent::setUp();
        $this->controller = new AddTagController();
        $this->controller->setTpl($this->pageBuilder);
        $this->controller->setConf($this->conf);
        $this->controller->setPluginManager($this->pluginManager);
        $this->controller->setLinkDB($this->linkDB);
    }

    /**
     * Test addtag without HTTP_REFERER.
     * Without it, user won't be able to add a new tag, and will search only the selected one.
     *
     * @runInSeparateProcess
     */
    public function testAddTagWithoutReferer()
    {
        $tag = 'php';
        $this->controller->setGet(array('addtag' => $tag));
        $this->assertTrue($this->controller->redirect());
        $headers = xdebug_get_headers();
        $this->assertTrue($this->isHeaderSetRegex('Location: \?searchtags='. $tag, $headers));
    }

    /**
     * Test addtag with HTTP_REFERER.
     * The new tag will be added to the other ones.
     *
     * @runInSeparateProcess
     */
    public function testAddTagWithReferer()
    {
        $tag1 = 'php';
        $tag2 = 'urĺènC0d>ẽ';
        $referer = 'http://domain.tld';

        $this->assertTrue($this->controller->redirect());
        $headers = xdebug_get_headers();
        $this->assertTrue($this->arrayContains('Location: ?searchtags=', $headers));

        $this->controller->setGet(array('addtag' => $tag1));
        $this->controller->setServer(array('HTTP_REFERER' => $referer));
        $this->assertTrue($this->controller->redirect());
        $headers = xdebug_get_headers();
        $this->assertTrue($this->arrayContains('Location: ?searchtags='. $tag1, $headers));

        // Redirection loop
        $referer = 'http://domain.tld/?addtag='. $tag2;
        $this->controller->setGet(array('addtag' => $tag2));
        $this->controller->setServer(array('HTTP_REFERER' => $referer));
        $this->assertTrue($this->controller->redirect());
        $headers = xdebug_get_headers();
        $this->assertTrue($this->arrayContains('Location: ?searchtags='. urlencode($tag2), $headers));

        $referer = 'http://domain.tld/?searchtags='. $tag1;
        $this->controller->setServer(array('HTTP_REFERER' => $referer));
        $this->controller->setGet(array('addtag' => $tag2));
        $this->assertTrue($this->controller->redirect());
        $headers = xdebug_get_headers();
        $this->assertTrue($this->arrayContains(
            'Location: ?searchtags='. $tag1 . urlencode(' '. $tag2),
            $headers
        ));
    }
}
