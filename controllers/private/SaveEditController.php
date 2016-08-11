<?php

/**
 * SaveEditController.php
 * Author: arthur
 */
class SaveEditController extends AuthenticatedController
{
    public function redirect()
    {
        if (parent::redirect()) {
            return true;
        }

        // Go away!
        if (! tokenOk($this->post['token'])) {
            die('Wrong token.');
        }

        // Remove multiple spaces.
        $tags = trim(preg_replace('/\s\s+/', ' ', $this->post['lf_tags']));
        // Remove first '-' char in tags.
        $tags = preg_replace('/(^| )\-/', '$1', $tags);
        // Remove duplicates.
        $tags = implode(' ', array_unique(explode(' ', $tags)));
        $linkdate = $this->post['lf_linkdate'];
        $url = trim($this->post['lf_url']);
        if (! startsWith($url, 'http:') && ! startsWith($url, 'https:')
            && ! startsWith($url, 'ftp:') && ! startsWith($url, 'magnet:')
            && ! startsWith($url, '?') && ! startsWith($url, 'javascript:')
        ) {
            $url = 'http://' . $url;
        }

        $link = array(
            'title' => trim($this->post['lf_title']),
            'url' => $url,
            'description' => $this->post['lf_description'],
            'private' => (isset($this->post['lf_private']) ? 1 : 0),
            'linkdate' => $linkdate,
            'tags' => str_replace(',', ' ', $tags)
        );
        // If title is empty, use the URL as title.
        if ($link['title'] == '') {
            $link['title'] = $link['url'];
        }

        $this->pluginManager->executeHooks('save_link', $link);

        $this->linkDB[$linkdate] = $link;
        $this->linkDB->savedb($this->conf->get('resource.page_cache'));
        pubsubhub($this->conf);

        // If we are called from the bookmarklet, we must close the popup:
        if (isset($this->get['source'])
            && ($this->get['source']=='bookmarklet' || $this->get['source']=='firefoxsocialapi')
        ) {
            echo '<script>self.close();</script>';
            return false;
        }

        $returnurl = !empty($this->post['returnurl']) ? $this->post['returnurl'] : '?';
        $location = generateLocation($returnurl, $this->server['HTTP_HOST'], array('addlink', 'post', 'edit_link'));
        // Scroll to the link which has been edited.
        $location .= '#' . smallHash($this->post['lf_linkdate']);
        // After saving the link, redirect to the page the user was on.
        header('Location: '. $location);
        return true;
    }


    public function render()
    {
        return;
    }

}