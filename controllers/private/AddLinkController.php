<?php

class AddLinkController extends AuthenticatedController
{
    public function render()
    {
        $this->tpl->renderPage('addlink');
    }
}