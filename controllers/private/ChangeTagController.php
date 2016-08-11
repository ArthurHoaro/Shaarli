<?php

class ChangeTagController extends AuthenticatedController
{
    public function render()
    {
        if (empty($this->post['fromtag']) || (empty($this->post['totag']) && isset($this->post['renametag']))) {
            $this->tpl->assign('tags', $this->linkDB->allTags());
            $this->tpl->renderPage('changetag');
            return;
        }

        if (!tokenOk($this->post['token'])) {
            die('Wrong token.');
        }

        // Delete a tag:
        if (isset($this->post['deletetag']) && !empty($this->post['fromtag'])) {
            $needle = trim($this->post['fromtag']);
            // True for case-sensitive tag search.
            $linksToAlter = $this->linkDB->filterSearch(array('searchtags' => $needle), true);
            foreach($linksToAlter as $key=>$value)
            {
                $tags = explode(' ',trim($value['tags']));
                unset($tags[array_search($needle,$tags)]); // Remove tag.
                $value['tags']=trim(implode(' ',$tags));
                $this->linkDB[$key]=$value;
            }
            $this->linkDB->savedb($this->conf->get('resource.page_cache'));
            echo '<script>alert("Tag was removed from '.count($linksToAlter).' links.");document.location=\'?\';</script>';
            return;
        }

        // Rename a tag:
        if (isset($this->post['renametag']) && !empty($this->post['fromtag']) && !empty($this->post['totag'])) {
            $needle = trim($this->post['fromtag']);
            // True for case-sensitive tag search.
            $linksToAlter = $this->linkDB->filterSearch(array('searchtags' => $needle), true);
            foreach($linksToAlter as $key=>$value)
            {
                $tags = explode(' ',trim($value['tags']));
                $tags[array_search($needle,$tags)] = trim($this->post['totag']); // Replace tags value.
                $value['tags']=trim(implode(' ',$tags));
                $this->linkDB[$key]=$value;
            }
            $this->linkDB->savedb($this->conf->get('resource.page_cache')); // Save to disk.
            echo '<script>alert("Tag was renamed in '.count($linksToAlter).' links.");document.location=\'?searchtags='.urlencode($this->post['totag']).'\';</script>';
            return;
        }
    }

}