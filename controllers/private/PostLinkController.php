<?php

class PostLinkController extends AuthenticatedController
{
    public function render()
    {
        $url = cleanup_url($this->get['post']);

        $link_is_new = false;
        // Check if URL is not already in database (in this case, we will edit the existing link)
        $link = $this->linkDB->getLinkFromUrl($url);
        if (!$link)
        {
            $link_is_new = true;
            $linkdate = strval(date('Ymd_His'));
            // Get title if it was provided in URL (by the bookmarklet).
            $title = empty($this->get['title']) ? '' : escape($this->get['title']);
            // Get description if it was provided in URL (by the bookmarklet). [Bronco added that]
            $description = empty($this->get['description']) ? '' : escape($this->get['description']);
            $tags = empty($this->get['tags']) ? '' : escape($this->get['tags']);
            $private = !empty($this->get['private']) && $this->get['private'] === "1" ? 1 : 0;
            // If this is an HTTP(S) link, we try go get the page to extract the title (otherwise we will to straight to the edit form.)
            if (empty($title) && strpos(get_url_scheme($url), 'http') !== false) {
                // Short timeout to keep the application responsive
                list($headers, $content) = get_http_response($url, 4);
                if (strpos($headers[0], '200 OK') !== false) {
                    // Retrieve charset.
                    $charset = get_charset($headers, $content);
                    // Extract title.
                    $title = html_extract_title($content);
                    // Re-encode title in utf-8 if necessary.
                    if (! empty($title) && strtolower($charset) != 'utf-8') {
                        $title = mb_convert_encoding($title, 'utf-8', $charset);
                    }
                }
            }

            if ($url == '') {
                $url = '?' . smallHash($linkdate);
                $title = 'Note: ';
            }
            $url = escape($url);
            $title = escape($title);

            $link = array(
                'linkdate' => $linkdate,
                'title' => $title,
                'url' => $url,
                'description' => $description,
                'tags' => $tags,
                'private' => $private
            );
        }

        $data = array(
            'link' => $link,
            'link_is_new' => $link_is_new,
            'http_referer' => (isset($this->server['HTTP_REFERER']) ? escape($this->server['HTTP_REFERER']) : ''),
            'source' => (isset($this->get['source']) ? $this->get['source'] : ''),
            'tags' => $this->linkDB->allTags(),
            'default_private_links' => $this->conf->get('privacy.default_private_links', false),
        );
        $this->pluginManager->executeHooks('render_editlink', $data);

        foreach ($data as $key => $value) {
            $this->tpl->assign($key, $value);
        }

        $this->tpl->renderPage('editlink');
    }
}