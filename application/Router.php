<?php

/**
 * Class Router
 */
class Router
{
    public static $PAGE_LOGIN = 'login';

    public static $PAGE_LOGOUT = 'logout';

    public static $PAGE_PICWALL = 'picWall';

    public static $PAGE_TAGCLOUD = 'tagCloud';

    public static $PAGE_DAILY = 'daily';

    public static $PAGE_FEED_ATOM = 'atom';

    public static $PAGE_FEED_RSS = 'rss';

    public static $PAGE_TOOLS = 'tools';

    public static $PAGE_CHANGEPASSWORD = 'changePassword';

    public static $PAGE_CONFIGURE = 'configure';

    public static $PAGE_CHANGETAG = 'changeTag';

    public static $PAGE_ADDLINK = 'addLink';

    public static $PAGE_EDITLINK = 'editLink';

    public static $PAGE_EXPORT = 'export';

    public static $PAGE_IMPORT = 'import';

    public static $PAGE_OPENSEARCH = 'openSearch';

    public static $ADDTAG = 'addtag';

    public static $REMOVETAG = 'removetag';

    public static $LINKSPERPAGE = 'linksPerPage';

    public static $PRIVATEONLY = 'privateOnly';

    public static $PAGE_LINKLIST = 'linklist';

    public static $PAGE_PLUGINSADMIN = 'pluginAdmin';

    public static $PAGE_SAVE_PLUGINSADMIN = 'savePluginAdmin';

    public static $SAVE_EDIT = 'saveEdit';
    public static $CANCEL_EDIT = 'cancelEdit';
    public static $DELETE_LINK = 'deleteLink';
    public static $POST_LINK = 'postLink';

    /**
     * @var array List of pages accessible via do=GET parameter
     *              - key: internal page name
     *              - value: GET parameter associated to the page
     */
    protected $doGetPages;

    /**
     * @var array List of pages accessible via GET parameters
     *              - key: internal page name
     *              - value: array required GET parameters.
     */
    protected $getPages;

    /**
     * @var array List of pages accessible via POST parameters
     *              - key: internal page name
     *              - value: array required POST parameters.
     */
    protected $postPages;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->doGetPages = array(
            self::$PAGE_LOGIN => 'login',
            self::$PAGE_LOGOUT => 'logout',
            self::$PAGE_PICWALL => 'picwall',
            self::$PAGE_TAGCLOUD => 'tagcloud',
            self::$PAGE_DAILY => 'daily',
            self::$PAGE_FEED_ATOM => 'atom',
            self::$PAGE_FEED_RSS => 'rss',
            self::$PAGE_TOOLS => 'tools',
            self::$PAGE_CHANGEPASSWORD => 'changepasswd',
            self::$PAGE_CONFIGURE => 'configure',
            self::$PAGE_CHANGETAG => 'changetag',
            self::$PAGE_ADDLINK => 'addlink',
            self::$PAGE_EXPORT => 'export',
            self::$PAGE_IMPORT => 'import',
            self::$PAGE_OPENSEARCH => 'opensearch',
            self::$PAGE_PLUGINSADMIN => 'pluginadmin',
            self::$PAGE_SAVE_PLUGINSADMIN => 'save_pluginadmin',
        );

        $this->getPages = array(
            self::$ADDTAG => array('addtag'),
            self::$REMOVETAG => array('removetag'),
            self::$LINKSPERPAGE => array('linksperpage'),
            self::$PRIVATEONLY => array('privateonly'),
            self::$POST_LINK => array('post'),
            self::$PAGE_EDITLINK => array('edit_link'),
        );

        $this->postPages = array(
            self::$SAVE_EDIT => array('save_edit'),
            self::$CANCEL_EDIT => array('cancel_edit'),
            self::$DELETE_LINK => array('delete_link'),
        );
    }

    /**
     * Reproducing renderPage() if hell, to avoid regression.
     *
     * This highlights how bad this needs to be rewrite,
     * but let's focus on plugins for now.
     *
     * @param array  $get      $_GET.
     * @param array  $post     $_POST.
     *
     * @return string page found.
     */
    public function findPage($get, $post)
    {
        if (!empty($get['do']) && ($pos = array_search($get['do'], $this->doGetPages)) !== false) {
            return $pos;
        }

        foreach ($this->getPages as $pageName => $getPage) {
            // If $_GET contains all parameters required in $getPage array, that's it.
            if (array_intersect($getPage, array_keys($get)) == $getPage) {
                return $pageName;
            }
        }

        foreach ($this->postPages as $pageName => $postPage) {
            // If $_POST contains all parameters required in $postPage array, that's it.
            if (array_intersect($postPage, array_keys($post)) == $postPage) {
                return $pageName;
            }
        }

        return self::$PAGE_LINKLIST;
    }
}
