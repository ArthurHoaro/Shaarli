<?php

namespace Shaarli\Formatter;

use Shaarli\Links\Link;

abstract class LinkFormatter
{
    /**
     * @param Link $link
     *
     * @return array
     */
    public function format($link)
    {
        $out['id'] = $link->getId();
        $out['shorturl'] = $link->getShortUrl();
        $out['url'] = $this->formatUrl($link->getUrl());
        $out['real_url'] = $this->formatUrl($link->getUrl());
        $out['title'] = $this->formatTitle($link->getTitle());
        $out['description'] = $this->formatDescription($link->getDescription());

        // wrap this in a method
        $out['created'] = $link->getCreated();
        $out['updated'] = $link->getUpdated();
        $out['timestamp'] = $link->getCreated()->getTimestamp();
        if (! empty($link->getUpdated())) {
            $out['updated_timestamp'] = $link->getUpdated()->getTimestamp();
        } else {
            $out['updated_timestamp'] = '';
        }

        $out['tags'] = $this->formatTags($link->getTags());
        $out['tagList'] = $this->formatTags($link->getTags());
        $out['private'] = $link->isPrivate();
        return $out;
    }

    public abstract function formatUrl($url);
    public abstract function formatTitle($title);
    public abstract function formatDescription($desc);
    public abstract function formatTags($tags);
}