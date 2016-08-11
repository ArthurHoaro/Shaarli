<?php

/**
 * SavePluginAdmin.php
 * Author: arthur
 */
class SavePluginAdminController extends AuthenticatedController
{
    public function render()
    {
        try {
            if (isset($this->post['parameters_form'])) {
                unset($this->post['parameters_form']);
                foreach ($this->post as $param => $value) {
                    $this->conf->set('plugins.'. $param, escape($value));
                }
            }
            else {
                $this->conf->set('general.enabled_plugins', save_plugin_config($this->post));
            }
            $this->conf->write(isLoggedIn());
        }
        catch (Exception $e) {
            error_log(
                'ERROR while saving plugin configuration:.' . PHP_EOL .
                $e->getMessage()
            );

            // TODO: do not handle exceptions/errors in JS.
            echo '<script>alert("'. $e->getMessage() .'");document.location=\'?do='. Router::$PAGE_PLUGINSADMIN .'\';</script>';
            exit;
        }
        header('Location: ?do='. Router::$PAGE_PLUGINSADMIN);
    }

}