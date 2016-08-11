<?php

/**
 * Class Controller.
 * 
 * This abstract class is design to handle page rendering.
 * All request to Shaarli must pass through its own controller.
 */
abstract class Controller
{
    /**
     * @var PageBuilder
     */
    protected $tpl;

    /**
     * @var ConfigManager
     */
    protected $conf;

    /**
     * @var PluginManager
     */
    protected $pluginManager;

    /**
     * @var LinkDB
     */
    protected $linkDB;

    /**
     * @var array $_SESSION
     */
    protected $session;

    /**
     * @var array $_SERVER
     */
    protected $server;

    /**
     * @var array
     */
    protected $get;

    /**
     * @var array
     */
    protected $post;

    /**
     * @var array
     */
    protected $files;

    /**
     * @var bool
     */
    protected $loggedIn;

    /**
     * Handle headers redirection.
     * 
     * @return boolean true if a redirection is necessary, false otherwise.
     */
    public abstract function redirect();

    /**
     * Render the view through the template engine.
     */
    public abstract function render();

    /**
     * @param PageBuilder $tpl
     */
    public function setTpl($tpl)
    {
        $this->tpl = $tpl;
    }

    /**
     * @param ConfigManager $conf
     */
    public function setConf($conf)
    {
        $this->conf = $conf;
    }

    /**
     * @param PluginManager $pluginManager
     */
    public function setPluginManager($pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /**
     * @param LinkDB $linkDB
     */
    public function setLinkDB($linkDB)
    {
        $this->linkDB = $linkDB;
    }

    /**
     * @param array $server
     */
    public function setServer($server)
    {
        $this->server = $server;
    }

    /**
     * @param array $get
     */
    public function setGet($get)
    {
        $this->get = $get;
    }

    /**
     * @param array $post
     */
    public function setPost($post)
    {
        $this->post = $post;
    }

    /**
     * @param array $session
     */
    public function setSession(&$session)
    {
        $this->session = &$session;
    }

    /**
     * @param array $files
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }

    /**
     * @param boolean $loggedIn
     */
    public function setLoggedIn($loggedIn)
    {
        $this->loggedIn = $loggedIn === true;
    }
}
