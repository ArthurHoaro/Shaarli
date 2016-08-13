<?php

/**
 * Class OpenSearchControllerTest
 *
 * Test the OpenSearch page controller.
 */
class OpenSearchControllerTest extends ControllerTest
{
    /**
     * Initialize the controller.
     */
    public function setUp()
    {
        parent::setUp();
        $this->controller = new OpenSearchController();
        $this->controller->setTpl($this->pageBuilder);
        $this->controller->setConf($this->conf);
        $this->controller->setPluginManager($this->pluginManager);
        $this->controller->setLinkDB($this->linkDB);
    }

    /**
     * Test redirect(): it shouldn't trigger any redirection.
     */
    public function testRedirect()
    {
        $this->assertFalse($this->controller->redirect());
    }

    /**
     * Test render(): make sure the open search template is displayed.
     *
     * @runInSeparateProcess
     */
    public function testRender()
    {
        // used by index_url()
        $server = array(
            'SERVER_NAME' => 'domain.tld',
            'SCRIPT_NAME' => '/',
            'QUERY_STRING' => '',
            'SERVER_PORT' => 80,
            'REQUEST_URI' => '',
        );
        $this->controller->setServer($server);

        $opensearchTag = '<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">';
        $indexUrl = 'http://domain.tld';
        $this->expectOutputRegex('#'. $opensearchTag .'.*'. $indexUrl .'#msi');
        $this->controller->render();
        $headers = xdebug_get_headers();
        $this->assertTrue($this->arrayContains('Content-Type: application/xml; charset=utf-8', $headers));
    }
}
