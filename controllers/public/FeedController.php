<?php

abstract class FeedController extends Controller
{
    /**
     * @var string Defined in subclasses (atom/rss).
     */
    protected static $feedType;

    public function redirect()
    {
    }

    public function render()
    {
        header('Content-Type: application/'. static::$feedType .'+xml; charset=utf-8');

        // Cache system
        $query = $_SERVER['QUERY_STRING'];
        $cache = new CachedPage(
            $this->conf->get('resource.page_cache'),
            page_url($_SERVER),
            startsWith($query,'do='. static::$feedType) && !isLoggedIn()
        );
        $cached = $cache->cachedVersion();
        if (!empty($cached)) {
            echo $cached;
            exit;
        }

        // Generate data.
        $feedGenerator = new FeedBuilder($this->linkDB, static::$feedType, $_SERVER, $_GET, isLoggedIn());
        $feedGenerator->setLocale(strtolower(setlocale(LC_COLLATE, 0)));
        $feedGenerator->setHideDates($this->conf->get('privacy.hide_timestamps') && !isLoggedIn());
        $feedGenerator->setUsePermalinks(isset($_GET['permalinks']) || !$this->conf->get('feed.rss_permalinks'));
        $pshUrl = $this->conf->get('config.PUBSUBHUB_URL');
        if (!empty($pshUrl)) {
            $feedGenerator->setPubsubhubUrl($pshUrl);
        }
        $data = $feedGenerator->buildData();

        // Process plugin hook.
        $this->pluginManager->executeHooks('render_feed', $data, array(
            'loggedin' => isLoggedIn(),
            'target' => static::$feedType,
        ));

        // Render the template.
        $this->tpl->assignAll($data);
        $this->tpl->renderPage('feed.'. static::$feedType);
        $cache->cache(ob_get_contents());
        ob_end_flush();
    }
}
