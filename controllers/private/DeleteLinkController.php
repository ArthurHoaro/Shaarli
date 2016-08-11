<?php

/**
 * DeleteLinkController.php
 * Author: arthur
 */
class DeleteLinkController extends AuthenticatedController
{
    public function redirect()
    {
        if (parent::redirect()) {
            return true;
        }

        if (!tokenOk($this->post['token'])) {
            die('Wrong token.');
        }

        // We do not need to ask for confirmation:
        // - confirmation is handled by JavaScript
        // - we are protected from XSRF by the token.
        $linkdate = $this->post['lf_linkdate'];

        $this->pluginManager->executeHooks('delete_link', $this->linkDB[$linkdate]);

        unset($this->linkDB[$linkdate]);
        $this->linkDB->savedb($this->conf->get('resource.page_cache')); // save to disk

        // If we are called from the bookmarklet, we must close the popup:
        if (isset($this->get['source']) && ($this->get['source']=='bookmarklet' || $this->get['source']=='firefoxsocialapi')) {
            echo '<script>self.close();</script>';
            return false;
        }
        
        // Pick where we're going to redirect
        // =============================================================
        // Basically, we can't redirect to where we were previously if it was a permalink
        // or an edit_link, because it would 404.
        // Cases:
        //    - /             : nothing in $this->get, redirect to self
        //    - /?page        : redirect to self
        //    - /?searchterm  : redirect to self (there might be other links)
        //    - /?searchtags  : redirect to self
        //    - /permalink    : redirect to / (the link does not exist anymore)
        //    - /?edit_link   : redirect to / (the link does not exist anymore)
        // PHP treats the permalink as a $this->get variable, so we need to check if every condition for self
        // redirect is not satisfied, and only then redirect to /
        $location = "?";
        // Self redirection
        if (count($this->get) == 0
            || isset($this->get['page'])
            || isset($this->get['searchterm'])
            || isset($this->get['searchtags'])
        ) {
            if (isset($this->post['returnurl'])) {
                $location = $this->post['returnurl']; // Handle redirects given by the form
            } else {
                $location = generateLocation($this->server['HTTP_REFERER'], $this->server['HTTP_HOST'], array('delete_link'));
            }
        }

        header('Location: ' . $location); // After deleting the link, redirect to appropriate location
        return true;
    }

    public function render()
    {
        // TODO: Implement render() method.
    }
}
