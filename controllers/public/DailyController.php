<?php

/**
 * Class DailyController
 *
 * Build the public Daily page.
 */
class DailyController extends Controller
{
    public function redirect()
    {
        return false;
    }

    public function render()
    {
        $day = date('Ymd', strtotime('-1 day')); // Yesterday, in format YYYYMMDD.
        if (isset($_GET['day'])) {
            $day = $_GET['day'];
        }

        $days = $this->linkDB->days();
        $i = array_search($day, $days);
        if ($i === false) {
            $i = count($days)-1;
            $day = $days[$i];
        }
        $previousday = '';
        $nextday = '';
        if ($i !== false) {
            if ($i >= 1) {
                $previousday = $days[$i - 1];
            }
            if ($i < count($days) - 1) {
                $nextday = $days[$i+1];
            }
        }

        try {
            $linksToDisplay = $this->linkDB->filterDay($day);
        } catch (Exception $exc) {
            error_log($exc);
            $linksToDisplay = array();
        }

        // We pre-format some fields for proper output.
        foreach ($linksToDisplay as $key=>$link)
        {
            $taglist = explode(' ',$link['tags']);
            uasort($taglist, 'strcasecmp');
            $linksToDisplay[$key]['taglist']=$taglist;
            $linksToDisplay[$key]['formatedDescription'] = format_description(
                $link['description'],
                $this->conf->get('redirector.url')
            );
            $linksToDisplay[$key]['thumbnail'] = thumbnail($this->conf, $link['url']);
            $date = DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, $link['linkdate']);
            $linksToDisplay[$key]['timestamp'] = $date->getTimestamp();
        }

        /* We need to spread the articles on 3 columns.
           I did not want to use a JavaScript lib like http://masonry.desandro.com/
           so I manually spread entries with a simple method: I roughly evaluate the
           height of a div according to title and description length.
        */
        // Entries to display, for each column.
        $columns = array(array(), array(), array());
        // Rough estimate of columns fill.
        $fill = array(0, 0, 0);
        foreach ($linksToDisplay as $key => $link)
        {
            // Roughly estimate length of entry (by counting characters)
            // Title: 30 chars = 1 line. 1 line is 30 pixels height.
            // Description: 836 characters gives roughly 342 pixel height.
            // This is not perfect, but it's usually OK.
            $length = strlen($link['title']) + (342 * strlen($link['description'])) / 836;
            // 1 thumbnails roughly takes 100 pixels height.
            if ($link['thumbnail']) {
                $length += 100;
            }
            // Then put in column which is the less filled:
            // find smallest value in array.
            $smallest = min($fill);
            // find index of this smallest value.
            $index = array_search($smallest, $fill);
            // Put entry in this column.
            array_push($columns[$index], $link);
            $fill[$index] += $length;
        }

        $dayDate = DateTime::createFromFormat(LinkDB::LINK_DATE_FORMAT, $day.'_000000');
        $data = array(
            'linksToDisplay' => $linksToDisplay,
            'cols' => $columns,
            'day' => $dayDate->getTimestamp(),
            'previousday' => $previousday,
            'nextday' => $nextday,
        );

        $this->pluginManager->executeHooks('render_daily', $data, array('loggedin' => $this->loggedIn));

        foreach ($data as $key => $value) {
            $this->tpl->assign($key, $value);
        }

        $this->tpl->renderPage('daily');
    }
}
