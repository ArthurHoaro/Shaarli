<?php

/**
 * Class AddTagController
 *
 * This controller is used to add an additional tag to the current search in the link list.
 */
class AddTagController extends Controller
{
    public function redirect()
    {
        // Get previous URL (http_referer) and add the tag to the searchtags parameters in query.
        // In case browser does not send HTTP_REFERER
        if (empty($this->server['HTTP_REFERER'])) {
            header('Location: ?searchtags=' . urlencode($this->get['addtag']));
            return true;
        }
        parse_str(parse_url($this->server['HTTP_REFERER'], PHP_URL_QUERY), $params);

        // Prevent redirection loop
        if (isset($params['addtag'])) {
            unset($params['addtag']);
        }

        // Check if this tag is already in the search query and ignore it if it is.
        // Each tag is always separated by a space
        if (isset($params['searchtags'])) {
            $current_tags = explode(' ', $params['searchtags']);
        } else {
            $current_tags = array();
        }
        $addtag = true;
        foreach ($current_tags as $value) {
            if ($value === $this->get['addtag']) {
                $addtag = false;
                break;
            }
        }
        // Append the tag if necessary
        if (empty($params['searchtags'])) {
            $params['searchtags'] = trim($this->get['addtag']);
        }
        else if ($addtag) {
            $params['searchtags'] = trim($params['searchtags']).' '.trim($this->get['addtag']);
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