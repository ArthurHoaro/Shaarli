<?php

/**
 * Class AuthenticatedController
 */
abstract class AuthenticatedController extends Controller
{
    public function redirect()
    {
        if (isLoggedIn()) {
            return false;
        }

        // User tries to post new link but is not logged in:
        // Show login screen, then redirect to ?post=...
        if (isset($this->get['post'])) {
            $location  = '?do=login&post=' . urlencode($this->get['post'])
                . (!empty($this->get['title']) ? '&title=' . urlencode($this->get['title']) : '')
                . (!empty($this->get['description']) ? '&description=' . urlencode($this->get['description']) : '')
                . (!empty($this->get['source']) ? '&source=' . urlencode($_GET['source']) : '');
            // Redirect to login page, then back to post link.
            header('Location: '. $location);
            return true;
        }


        if (isset($this->get['edit_link'])) {
            header('Location: ?do=login&edit_link='. escape($this->get['edit_link']));
            return true;
        }

        // CHANGE HERE: we redirect to the linklist instead of showing it directly.
        // showLinkList($PAGE, $LINKSDB, $conf, $pluginManager);
        header('Location: ?');
        return true;
    }
}