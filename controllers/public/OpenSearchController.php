<?php

/**
 * Class OpenSearchController
 *
 * Render the opensearch template.
 * 
 * From Wikipedia:
 *   OpenSearch is a collection of technologies that allow publishing of search results 
 *   in a format suitable for syndication and aggregation. It is a way for websites and 
 *   search engines to publish search results in a standard and accessible format.
 * 
 * Example usage: 
 *   It allows to add a Shaarli's instance as a search engine in Firefox.
 *
 * @see http://www.opensearch.org/Home
 * @see https://github.com/shaarli/Shaarli/issues/176
 */
class OpenSearchController extends Controller
{
    public function redirect()
    {
        return false;
    }

    public function render()
    {
        header('Content-Type: application/xml; charset=utf-8');
        $this->tpl->assign('serverurl', index_url($this->server));
        $this->tpl->renderPage('opensearch');
    }
}
