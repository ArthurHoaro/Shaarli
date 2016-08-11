<?php


class LinksPerPageController extends Controller
{
    public function redirect()
    {
        if (is_numeric($this->get['linksperpage'])) {
            $this->session['LINKS_PER_PAGE'] = abs(intval($this->get['linksperpage']));
        }

        $location = generateLocation($this->server['HTTP_REFERER'], $this->server['HTTP_HOST'], array('linksperpage'));
        header('Location: ' . $location);
    }

    public function render()
    {
        return false;
    }

}