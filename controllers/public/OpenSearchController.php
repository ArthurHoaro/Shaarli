<?php


class OpenSearchController extends Controller
{
    public function redirect()
    {
        return false;
    }

    public function render()
    {
        header('Content-Type: application/xml; charset=utf-8');
        $this->tpl->assign('serverurl', index_url($this->server));
        $this->tpl->renderPage('opensearch');
    }
}
