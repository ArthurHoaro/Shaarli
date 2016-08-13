<?php

/**
 * Class FeedController
 * 
 * Abstract class handling RSS and ATOM feed rendering.
 */
abstract class FeedController extends Controller
{
    /**
     * @var string Defined in subclasses (atom/rss).
     */
    protected static $feedType;

    public function redirect()
    {
        header('Content-Type: application/'. static::$feedType .'+xml; charset=utf-8');
        return false;
    }

    public function render()
    {
        // Cache system
        $query = $this->server['QUERY_STRING'];
        $cache = new CachedPage(
            $this->conf->get('resource.page_cache'),
            page_url($this->server),
            startsWith($query,'do='. static::$feedType) && !$this->loggedIn
        );
        $cached = $cache->cachedVersion();
        if (!empty($cached)) {
            echo $cached;
            return;
        }

        // Generate data.
        $feedGenerator = new FeedBuilder($this->linkDB, static::$feedType, $this->server, $this->get, $this->loggedIn);
        $feedGenerator->setLocale(strtolower(setlocale(LC_COLLATE, 0)));
        $feedGenerator->setHideDates($this->conf->get('privacy.hide_timestamps') && !$this->loggedIn);
        $feedGenerator->setUsePermalinks(isset($this->get['permalinks']) || !$this->conf->get('feed.rss_permalinks'));
        $pshUrl = $this->conf->get('config.PUBSUBHUB_URL');
        if (!empty($pshUrl)) {
            $feedGenerator->setPubsubhubUrl($pshUrl);
        }
        $data = $feedGenerator->buildData();

        // Process plugin hook.
        $this->pluginManager->executeHooks('render_feed', $data, array(
            'loggedin' => $this->loggedIn,
            'target' => static::$feedType,
        ));

        // Render the template.
        $this->tpl->assignAll($data);
        $this->tpl->renderPage('feed.'. static::$feedType);
        $cache->cache(ob_get_contents());
    }
}
