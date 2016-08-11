<?php

class PrivateOnlyController extends AuthenticatedController
{
    public function redirect()
    {
        if (empty($this->session['privateonly'])) {
            // See only private links
            $this->session['privateonly'] = 1;
        } else {
            // See all links
            unset($this->session['privateonly']);
        }

        $location = generateLocation($this->server['HTTP_REFERER'], $this->server['HTTP_HOST'], array('privateonly'));
        header('Location: ' . $location);
        return true;
    }

    public function render()
    {
        // TODO: Implement render() method.
    }
}