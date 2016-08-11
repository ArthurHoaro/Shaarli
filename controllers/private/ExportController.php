<?php


class ExportController extends AuthenticatedController
{
    public function render()
    {
        // Export links as a Netscape Bookmarks file
        if (empty($this->get['selection'])) {
            $this->tpl->renderPage('export');
            return;
        }

        // export as bookmarks_(all|private|public)_YYYYmmdd_HHMMSS.html
        $selection = $this->get['selection'];
        if (isset($this->get['prepend_note_url'])) {
            $prependNoteUrl = $this->get['prepend_note_url'];
        } else {
            $prependNoteUrl = false;
        }

        try {
            $this->tpl->assign(
                'links',
                NetscapeBookmarkUtils::filterAndFormat(
                    $this->linkDB,
                    $selection,
                    $prependNoteUrl,
                    index_url($this->server)
                )
            );
        } catch (Exception $exc) {
            header('Content-Type: text/plain; charset=utf-8');
            echo $exc->getMessage();
            return;
        }
        $now = new DateTime();
        header('Content-Type: text/html; charset=utf-8');
        header(
            'Content-disposition: attachment; filename=bookmarks_'
            .$selection.'_'.$now->format(LinkDB::LINK_DATE_FORMAT).'.html'
        );
        $this->tpl->assign('date', $now->format(DateTime::RFC822));
        $this->tpl->assign('eol', PHP_EOL);
        $this->tpl->assign('selection', $selection);
        $this->tpl->renderPage('export.bookmarks');
    }

}