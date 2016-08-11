<?php

class ImportController extends AuthenticatedController
{
    public function render()
    {
        // Upload a Netscape bookmark dump to import its contents
        if (! isset($this->post['token']) || ! isset($_FILES['filetoupload'])) {
            // Show import dialog
            $this->tpl->assign('maxfilesize', getMaxFileSize());
            $this->tpl->renderPage('import');
            exit;
        }
        // Import bookmarks from an uploaded file
        if (isset($_FILES['filetoupload']['size']) && $_FILES['filetoupload']['size'] == 0) {
            // The file is too big or some form field may be missing.
            echo '<script>alert("The file you are trying to upload is probably'
                .' bigger than what this webserver can accept ('
                .getMaxFileSize().' bytes).'
                .' Please upload in smaller chunks.");document.location=\'?do='
                .Router::$PAGE_IMPORT .'\';</script>';
            exit;
        }
        if (! tokenOk($this->post['token'])) {
            die('Wrong token.');
        }
        $status = NetscapeBookmarkUtils::import(
            $this->post,
            $_FILES,
            $this->linkDB,
            $this->conf->get('resource.page_cache')
        );
        echo '<script>alert("'.$status.'");document.location=\'?do='
            .Router::$PAGE_IMPORT .'\';</script>';
    }
}