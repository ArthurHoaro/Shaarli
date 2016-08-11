<?php

/**
 * LinklistController.php
 * Author: arthur
 */
class LinklistController extends Controller
{
    public function redirect()
    {
        // TODO: Implement redirect() method.
    }

    public function render()
    {
        showLinkList($this->tpl, $this->linkDB, $this->conf, $this->pluginManager);
    }

}