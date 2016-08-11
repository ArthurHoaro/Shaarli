<?php

/**
 * EditLinkController.php
 * Author: arthur
 */
class EditLinkController extends AuthenticatedController
{
    protected $link;

    public function redirect()
    {
        if (parent::redirect()) {
            return true;
        }

        $this->link = $this->linkDB[$this->get['edit_link']];  // Read database
        // Link not found in database.
        if (!$this->link) {
            header('Location: ?');
            return true;
        }
        return false;
    }


    public function render()
    {
        $data = array(
            'link' => $this->link,
            'link_is_new' => false,
            'http_referer' => (isset($this->server['HTTP_REFERER']) ? escape($this->server['HTTP_REFERER']) : ''),
            'tags' => $this->linkDB->allTags(),
        );
        $this->pluginManager->executeHooks('render_editlink', $data);

        foreach ($data as $key => $value) {
            $this->tpl->assign($key, $value);
        }

        $this->tpl->renderPage('editlink');
    }
}