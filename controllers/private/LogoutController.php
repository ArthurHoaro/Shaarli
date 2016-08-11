<?php

/**
 * Class LogoutController
 */
class LogoutController extends Controller
{
    public function redirect()
    {
        invalidateCaches($this->conf->get('resource.page_cache'));
        logout();
        header('Location: ?');
        return true;
    }

    public function render()
    {
        return false;
    }

}