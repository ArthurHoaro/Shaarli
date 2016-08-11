<?php

/**
 * Class RemovetagController
 *
 * This controller is used to remove a tag to the current search in the link list.
 */
class RemoveTagController extends Controller
{
    public function redirect()
    {
        // Get previous URL (http_referer) and remove the tag from the searchtags parameters in query.
        if (empty($this->server['HTTP_REFERER'])) {
            header('Location: ?');
            return true;
        }

        // In case browser does not send HTTP_REFERER
        parse_str(parse_url($this->server['HTTP_REFERER'], PHP_URL_QUERY), $params);

        // Prevent redirection loop
        if (isset($params['removetag'])) {
            unset($params['removetag']);
        }

        if (isset($params['searchtags'])) {
            $tags = explode(' ', $params['searchtags']);
            // Remove value from array $tags.
            $tags = array_diff($tags, array($this->get['removetag']));
            $params['searchtags'] = implode(' ', $tags);

            if (empty($params['searchtags'])) {
                unset($params['searchtags']);
            }
        }
        
        // We also remove page (keeping the same page has no sense, since the results are different)
        unset($params['page']);
        header('Location: ?' . http_build_query($params));
        return true;
    }

    public function render()
    {
        return;
    }

}