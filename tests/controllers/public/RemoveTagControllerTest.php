<?php

/**
 * Class AddTagControllerTest
 */
class RemoveTagControllerTest extends ControllerTest
{
    /**
     * Initialize the controller.
     */
    public function setUp()
    {
        parent::setUp();
        $this->controller = new RemoveTagController();
        $this->controller->setTpl($this->pageBuilder);
        $this->controller->setConf($this->conf);
        $this->controller->setPluginManager($this->pluginManager);
        $this->controller->setLinkDB($this->linkDB);
    }

    /**
     * Test removetag without HTTP_REFERER.
     * Without it, user won't be able to use this, and will be redirected to the link list.
     *
     * @runInSeparateProcess
     */
    public function testRemoveTagWithoutReferer()
    {
        $this->controller->setGet(array('removetag' => 'test'));
        $this->assertTrue($this->controller->redirect());
        $headers = xdebug_get_headers();
        $this->assertTrue($this->isHeaderSetRegex('Location: \?$', $headers));
    }

    /**
     * Test addtag with HTTP_REFERER.
     * Remove tags in the existing search list.
     *
     * @runInSeparateProcess
     */
    public function testAddTagWithReferer()
    {
        $tag1 = 'php';
        $tag2 = 'urĺènC0d>ẽ';

        // no delete
        $referer = 'http://domain.tld/?searchtags='. $tag1;
        $this->controller->setServer(array('HTTP_REFERER' => $referer));
        $this->assertTrue($this->controller->redirect());
        $headers = xdebug_get_headers();
        $this->assertTrue($this->isHeaderSetRegex('Location: \?searchtags='. $tag1, $headers));

        // loop
        $referer = 'http://domain.tld/?removetag='. $tag1;
        $this->controller->setServer(array('HTTP_REFERER' => $referer));
        $this->assertTrue($this->controller->redirect());
        $headers = xdebug_get_headers();
        $this->assertFalse($this->arrayContains('removetag', $headers));

        // delete the last remaining
        $referer = 'http://domain.tld/?searchtags='. $tag1;
        $this->controller->setServer(array('HTTP_REFERER' => $referer));
        $this->controller->setGet(array('removetag' => $tag1));
        $this->assertTrue($this->controller->redirect());
        $headers = xdebug_get_headers();
        $this->assertTrue($this->isHeaderSetRegex('Location: \?$', $headers));

        // delete existing one
        $referer = 'http://domain.tld/?searchtags='. urlencode($tag2 . ' ') . $tag1;
        $this->controller->setServer(array('HTTP_REFERER' => $referer));
        $this->controller->setGet(array('removetag' => $tag2));
        $this->assertTrue($this->controller->redirect());
        $headers = xdebug_get_headers();
        $this->assertTrue($this->isHeaderSetRegex('Location: \?searchtags='. $tag1 .'$', $headers));
    }
}
