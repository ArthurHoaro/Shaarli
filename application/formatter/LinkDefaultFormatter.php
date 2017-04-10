<?php


namespace Shaarli\Formatter;


use Shaarli\Config\ConfigManager;

class LinkDefaultFormatter extends LinkFormatter
{
    /**
     * @var ConfigManager
     */
    protected $conf;

    /**
     * LinkDefaultFormatter constructor.
     * @param ConfigManager $conf
     */
    public function __construct(ConfigManager $conf)
    {
        $this->conf = $conf;
    }

    public function formatTitle($title)
    {
        return $title;
    }

    public function formatDescription($desc)
    {
        return format_description($desc);
    }

    public function formatTags($tags)
    {
        // todo uasort($taglist, 'strcasecmp');
        return preg_split('/\s+/', $tags, -1, PREG_SPLIT_NO_EMPTY);
    }

    public function formatUrl($url)
    {
        // TODO: Implement formatUrl() method.
    }


}