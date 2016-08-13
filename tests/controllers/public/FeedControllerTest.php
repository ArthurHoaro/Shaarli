<?php

require_once 'inc/rain.tpl.class.php';

/**
 * Class FeedControllerTest
 *
 * Unit test for FeedController, AtomController and RssController.
 * Most tests done on AtomController, RssController only for specific differences.
 */
class FeedControllerTest extends ControllerTest
{
    /**
     * @var string Cache directory.
     */
    public static $cacheDir = 'sandbox/pagecache';

    /**
     * @var RssController instance
     */
    protected $rssController;

    /**
     * Initialize the controller.
     */
    public function setUp()
    {
        parent::setUp();
        $this->controller = new AtomController();
        $this->controller->setTpl($this->pageBuilder);
        $this->controller->setConf($this->conf);
        $this->controller->setPluginManager($this->pluginManager);
        $this->controller->setLinkDB($this->linkDB);

        $this->rssController = new RssController();
        $this->rssController->setTpl($this->pageBuilder);
        $this->rssController->setConf($this->conf);
        $this->rssController->setPluginManager($this->pluginManager);
        $this->rssController->setLinkDB($this->linkDB);

        @mkdir(self::$cacheDir);
        $this->conf->set('resource.page_cache', self::$cacheDir);
    }

    /**
     * Test redirect(), check the content type header.
     *
     * @runInSeparateProcess
     */
    public function testControllerRedirect()
    {
        $this->assertFalse($this->controller->redirect());
        $headers = xdebug_get_headers();
        $this->assertTrue($this->arrayContains('Content-Type: application/atom+xml; charset=utf-8', $headers));
        $this->assertFalse($this->rssController->redirect());
        $headers = xdebug_get_headers();
        $this->assertTrue($this->arrayContains('Content-Type: application/rss+xml; charset=utf-8', $headers));
    }

    /**
     * Test render() using an existing cache.
     */
    public function testRenderCache()
    {
        $server = array(
            'SERVER_NAME' => 'domain.tld',
            'SCRIPT_NAME' => '/',
            'QUERY_STRING' => 'do=atom',
            'SERVER_PORT' => 80,
        );
        $this->controller->setServer($server);

        $url = 'http://domain.tld/?do=atom';
        $cacheFile = self::$cacheDir .'/'. sha1($url) .'.cache';
        $content = 'foobar';
        file_put_contents($cacheFile, $content);

        $this->expectOutputString($content);
        $this->controller->render();
        @unlink($cacheFile);
    }

    /**
     * Test render() Atom feed.
     */
    public function testRenderSimpleAtom()
    {
        $url = 'http://domain.tld/?do=atom';
        $cacheFile = self::$cacheDir .'/'. sha1($url) .'.cache';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }

        $server = array(
            'SERVER_NAME' => 'domain.tld',
            'SCRIPT_NAME' => '/',
            'QUERY_STRING' => 'do=atom',
            'SERVER_PORT' => 80,
            'REQUEST_URI' => '',
        );
        $this->controller->setServer($server);
        $this->expectOutputRegex('?<feed xmlns="http://www.w3.org/2005/Atom">(.*<entry>.*</entry>){7}?ms');
        $this->controller->render();

        // Make sure the cache file has been created.
        $this->assertFileExists($cacheFile);
        unlink($cacheFile);
    }

    /**
     * Test render() RSS feed.
     */
    public function testRenderSimpleRss()
    {
        $server = array(
            'SERVER_NAME' => 'domain.tld',
            'SCRIPT_NAME' => '/',
            'QUERY_STRING' => 'do=rss',
            'SERVER_PORT' => 80,
            'REQUEST_URI' => '',
        );
        $this->rssController->setServer($server);
        $rss = '<rss version="2.0" '.
            'xmlns:content="http://purl.org/rss/1.0/modules/content/" '.
            'xmlns:atom="http://www.w3.org/2005/Atom">';
        $this->expectOutputRegex('?'. $rss .'(.*<item>.*</item>){7}?ms');
        $this->rssController->render();
    }
}
