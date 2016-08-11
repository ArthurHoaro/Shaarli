<?php

/**
 * Router tests
 */

require_once 'application/Router.php';

/**
 * Unit tests for Router
 */
class RouterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Router instance.
     */
    protected $router;

    public function setUp()
    {
        $this->router = new Router();
    }

    /**
     * Test findPage: login page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageLoginValid()
    {
        $this->assertEquals(
            Router::$PAGE_LOGIN,
            $this->router->findPage(array('do' => 'login'), array())
        );

        $this->assertEquals(
            Router::$PAGE_LOGIN,
            $this->router->findPage(
                array(
                    'other' => 'parameter',
                    'do' => 'login',
                ),
                array()
            )
        );

        $this->assertEquals(
            Router::$PAGE_LOGIN,
            $this->router->findPage(array('do' => 'login'), array('post' => 'parameter'))
        );
    }

    /**
     * Test findPage: login page output.
     * Invalid: page shouldn't be return.
     *
     * @return void
     */
    public function testFindPageLoginInvalid()
    {
        $this->assertNotEquals(
            Router::$PAGE_LOGIN,
            $this->router->findPage(array('do' => 'loginlol'), array())
        );

        $this->assertNotEquals(
            Router::$PAGE_LOGIN,
            $this->router->findPage(array('do' => 'other'), array())
        );

        $this->assertNotEquals(
            Router::$PAGE_LOGIN,
            $this->router->findPage(array('other' => 'login'), array())
        );
    }

    /**
     * Test findPage: logout page output.
     */
    public function testFindPageLogout()
    {
        $this->assertEquals(
            Router::$PAGE_LOGOUT,
            $this->router->findPage(array('do' => 'logout'), array())
        );
    }

    /**
     * Test findPage: picwall page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPagePicwallValid()
    {
        $this->assertEquals(
            Router::$PAGE_PICWALL,
            $this->router->findPage(array('do' => 'picwall'), array())
        );
    }

    /**
     * Test findPage: picwall page output.
     * Invalid: page shouldn't be return.
     *
     * @return void
     */
    public function testFindPagePicwallInvalid()
    {
        $this->assertNotEquals(
            Router::$PAGE_PICWALL,
            $this->router->findPage(array('do' => 'other'), array())
        );
    }

    /**
     * Test findPage: tagcloud page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageTagcloudValid()
    {
        $this->assertEquals(
            Router::$PAGE_TAGCLOUD,
            $this->router->findPage(array('do' => 'tagcloud'), array())
        );
    }

    /**
     * Test findPage: daily page output.
     */
    public function testFindPageDaily()
    {
        $this->assertEquals(
            Router::$PAGE_DAILY,
            $this->router->findPage(array('do' => 'daily'), array())
        );
    }

    /**
     * Test findPage: atom page output.
     */
    public function testFindPageAtom()
    {
        $this->assertEquals(
            Router::$PAGE_FEED_ATOM,
            $this->router->findPage(array('do' => 'atom'), array())
        );
    }

    /**
     * Test findPage: rss page output.
     */
    public function testFindPageRss()
    {
        $this->assertEquals(
            Router::$PAGE_FEED_RSS,
            $this->router->findPage(array('do' => 'rss'), array())
        );
    }

    /**
     * Test findPage: linklist page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageLinklistValid()
    {
        $this->assertEquals(
            Router::$PAGE_LINKLIST,
            $this->router->findPage(array(), array())
        );

        $this->assertEquals(
            Router::$PAGE_LINKLIST,
            $this->router->findPage(array('whatever'), array())
        );

        $this->assertEquals(
            Router::$PAGE_LINKLIST,
            $this->router->findPage(array(), array('whatever'))
        );
    }

    /**
     * Test findPage: tools page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageToolsValid()
    {
        $this->assertEquals(
            Router::$PAGE_TOOLS,
            $this->router->findPage(array('do' => 'tools'), array())
        );
    }

    /**
     * Test findPage: changepasswd page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageChangepasswdValid()
    {
        $this->assertEquals(
            Router::$PAGE_CHANGEPASSWORD,
            $this->router->findPage(array('do' => 'changepasswd'), array())
        );
    }

    /**
     * Test findPage: configure page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageConfigureValid()
    {
        $this->assertEquals(
            Router::$PAGE_CONFIGURE,
            $this->router->findPage(array('do' => 'configure'), array())
        );
    }

    /**
     * Test findPage: changetag page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageChangetagValid()
    {
        $this->assertEquals(
            Router::$PAGE_CHANGETAG,
            $this->router->findPage(array('do' => 'changetag'), array())
        );
    }

    /**
     * Test findPage: addlink page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageAddlinkValid()
    {
        $this->assertEquals(
            Router::$PAGE_ADDLINK,
            $this->router->findPage(array('do' => 'addlink'), array())
        );
    }

    /**
     * Test findPage: export page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageExportValid()
    {
        $this->assertEquals(
            Router::$PAGE_EXPORT,
            $this->router->findPage(array('do' => 'export'), array())
        );
    }

    /**
     * Test findPage: import page output.
     * Valid: page should be return.
     *
     * @return void
     */
    public function testFindPageImportValid()
    {
        $this->assertEquals(
            Router::$PAGE_IMPORT,
            $this->router->findPage(array('do' => 'import'), array())
        );
    }

    /**
     * Test findPage: opensearch page output.
     */
    public function testFindPageOpenSearch()
    {
        $this->assertEquals(
            Router::$PAGE_OPENSEARCH,
            $this->router->findPage(array('do' => 'opensearch'), array())
        );
    }

    /**
     * Test findPage: pluginadmin page output.
     */
    public function testFindPagePluginAdmin()
    {
        $this->assertEquals(
            Router::$PAGE_PLUGINSADMIN,
            $this->router->findPage(array('do' => 'pluginadmin'), array())
        );
    }

    /**
     * Test findPage: save_pluginadmin page output.
     */
    public function testFindPageSavePluginAdmin()
    {
        $this->assertEquals(
            Router::$PAGE_SAVE_PLUGINSADMIN,
            $this->router->findPage(array('do' => 'save_pluginadmin'), array())
        );
    }

    /**
     * Test findPage: editlink page output.
     * Valid: page should be return.
     */
    public function testFindPageEditlinkValid()
    {
        $this->assertEquals(
            Router::$PAGE_EDITLINK,
            $this->router->findPage(array('edit_link' => 1), array())
        );

        $this->assertEquals(
            Router::$PAGE_EDITLINK,
            $this->router->findPage(
                array(
                    'do' => '',
                    'edit_link' => 1,
                ),
                array('other' => 'parameter')
            )
        );
    }

    /**
     * Test findPage: addtag page output.
     */
    public function testFindPageAddTag()
    {
        $this->assertEquals(
            Router::$ADDTAG,
            $this->router->findPage(array('addtag' => 'tag'), array())
        );
    }

    /**
     * Test findPage: removetag page output.
     */
    public function testFindPageRemoveTag()
    {
        $this->assertEquals(
            Router::$REMOVETAG,
            $this->router->findPage(array('removetag' => 'tag'), array())
        );
    }

    /**
     * Test findPage: removetag page output.
     */
    public function testFindPageLinksPerPage()
    {
        $this->assertEquals(
            Router::$LINKSPERPAGE,
            $this->router->findPage(array('linksperpage' => 'tag'), array())
        );
    }

    /**
     * Test findPage: privateonly page output.
     */
    public function testFindPagePrivateOnly()
    {
        $this->assertEquals(
            Router::$PRIVATEONLY,
            $this->router->findPage(array('privateonly' => ''), array())
        );
    }

    /**
     * Test findPage: post page output.
     */
    public function testFindPagePost()
    {
        $this->assertEquals(
            Router::$POST_LINK,
            $this->router->findPage(array('post' => ''), array())
        );
    }

    /**
     * Test findPage: save_edit page output.
     */
    public function testFindPageSaveEdit()
    {
        $this->assertEquals(
            Router::$SAVE_EDIT,
            $this->router->findPage(array(), array('save_edit' => ''))
        );

        $this->assertEquals(
            Router::$SAVE_EDIT,
            $this->router->findPage(array('do'), array('save_edit' => ''))
        );

        $this->assertEquals(
            Router::$SAVE_EDIT,
            $this->router->findPage(
                array('do'),
                array(
                    'save_edit' => '',
                    'other' => 'parameter',
                )
            )
        );
    }

    /**
     * Test findPage: cancel_edit page output.
     */
    public function testFindPageCancelEdit()
    {
        $this->assertEquals(
            Router::$CANCEL_EDIT,
            $this->router->findPage(array(), array('cancel_edit' => ''))
        );
    }

    /**
     * Test findPage: delete_link page output.
     */
    public function testFindPageDeleteLink()
    {
        $this->assertEquals(
            Router::$DELETE_LINK,
            $this->router->findPage(array(), array('delete_link' => ''))
        );
    }
}
