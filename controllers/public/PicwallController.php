<?php

/**
 * Class PicwallController
 *
 * Renders the picwall. Can be filtered with search results.
 */
class PicwallController extends Controller
{
    public function redirect()
    {
        return false;
    }

    public function render()
    {
        // Optionally filter the results:
        $links = $this->linkDB->filterSearch($this->get);
        $linksToDisplay = array();

        // Get only links which have a thumbnail.
        foreach($links as $link)
        {
            $permalink = '?' . escape(smallHash($link['linkdate']));
            $thumb = lazyThumbnail($this->conf, $link['url'], $permalink);
            // Only output links which have a thumbnail.
            if ($thumb != '')
            {
                $link['thumbnail'] = $thumb; // Thumbnail HTML code.
                $linksToDisplay[] = $link; // Add to array.
            }
        }

        $data = array(
            'linksToDisplay' => $linksToDisplay,
        );
        $this->pluginManager->executeHooks('render_picwall', $data, array('loggedin' => isLoggedIn()));

        foreach ($data as $key => $value) {
            $this->tpl->assign($key, $value);
        }

        $this->tpl->renderPage('picwall');
    }
}
