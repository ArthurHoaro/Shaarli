<?php

/**
 * CancelEditController.php
 * Author: arthur
 */
class CancelEditController extends AuthenticatedController
{
    public function redirect()
    {
        if (parent::redirect()) {
            return true;
        }

        // If we are called from the bookmarklet, we must close the popup:
        if (isset($this->get['source'])
            && ($this->get['source']=='bookmarklet' || $this->get['source']=='firefoxsocialapi')
        ) {
            echo '<script>self.close();</script>';
            return false;
        }

        $returnurl = ( isset($this->post['returnurl']) ? $this->post['returnurl'] : '?' );
        $returnurl .= '#'.smallHash($this->post['lf_linkdate']);  // Scroll to the link which has been edited.
        $returnurl = generateLocation($returnurl, $this->server['HTTP_HOST'], array('addlink', 'post', 'edit_link'));
        header('Location: '.$returnurl); // After canceling, redirect to the page the user was on.
        return true;
    }

    public function render()
    {
        // TODO: Implement render() method.
    }

}