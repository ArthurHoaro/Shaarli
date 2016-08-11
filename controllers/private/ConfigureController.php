<?php

class ConfigureController extends AuthenticatedController
{
    public function render()
    {
        if (!empty($this->post['title']) )
        {
            if (!tokenOk($this->post['token'])) {
                die('Wrong token.'); // Go away!
            }
            $tz = 'UTC';
            if (!empty($this->post['continent']) && !empty($this->post['city'])
                && isTimeZoneValid($this->post['continent'], $this->post['city'])
            ) {
                $tz = $this->post['continent'] . '/' . $this->post['city'];
            }
            $this->conf->set('general.timezone', $tz);
            $this->conf->set('general.title', escape($this->post['title']));
            $this->conf->set('general.header_link', escape($this->post['titleLink']));
            $this->conf->set('redirector.url', escape($this->post['redirector']));
            $this->conf->set('security.session_protection_disabled', !empty($this->post['disablesessionprotection']));
            $this->conf->set('privacy.default_private_links', !empty($this->post['privateLinkByDefault']));
            $this->conf->set('feed.rss_permalinks', !empty($this->post['enableRssPermalinks']));
            $this->conf->set('updates.check_updates', !empty($this->post['updateCheck']));
            $this->conf->set('privacy.hide_public_links', !empty($this->post['hidePublicLinks']));
            try {
                $this->conf->write(isLoggedIn());
            }
            catch(Exception $e) {
                error_log(
                    'ERROR while writing config file after configuration update.' . PHP_EOL .
                    $e->getMessage()
                );

                // TODO: do not handle exceptions/errors in JS.
                echo '<script>alert("'. $e->getMessage() .'");document.location=\'?do=configure\';</script>';
            }
            echo '<script>alert("Configuration was saved.");document.location=\'?do=configure\';</script>';
        }
        else // Show the configuration form.
        {
            $this->tpl->assign('title', $this->conf->get('general.title'));
            $this->tpl->assign('redirector', $this->conf->get('redirector.url'));
            list($timezone_form, $timezone_js) = generateTimeZoneForm($this->conf->get('general.timezone'));
            $this->tpl->assign('timezone_form', $timezone_form);
            $this->tpl->assign('timezone_js',$timezone_js);
            $this->tpl->assign('private_links_default', $this->conf->get('privacy.default_private_links', false));
            $this->tpl->assign('session_protection_disabled', $this->conf->get('security.session_protection_disabled', false));
            $this->tpl->assign('enable_rss_permalinks', $this->conf->get('feed.rss_permalinks', false));
            $this->tpl->assign('enable_update_check', $this->conf->get('updates.check_updates', true));
            $this->tpl->assign('hide_public_links', $this->conf->get('privacy.hide_public_links', false));
            $this->tpl->renderPage('configure');
        }
    }
}