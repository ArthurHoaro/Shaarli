<?php


class TagCloudController extends Controller
{
    public function redirect()
    {
        return false;
    }

    public function render()
    {
        $tags = $this->linkDB->allTags();

        // We sort tags alphabetically, then choose a font size according to count.
        // First, find max value.
        $maxcount = 0;
        foreach ($tags as $value) {
            $maxcount = max($maxcount, $value);
        }

        // Sort tags alphabetically: case insensitive, support locale if avalaible.
        uksort($tags, function($a, $b) {
            // Collator is part of PHP intl.
            if (class_exists('Collator')) {
                $c = new Collator(setlocale(LC_COLLATE, 0));
                if (!intl_is_failure(intl_get_error_code())) {
                    return $c->compare($a, $b);
                }
            }
            return strcasecmp($a, $b);
        });

        $tagList = array();
        foreach($tags as $key => $value) {
            // Tag font size scaling:
            //   default 15 and 30 logarithm bases affect scaling,
            //   22 and 6 are arbitrary font sizes for max and min sizes.
            $size = log($value, 15) / log($maxcount, 30) * 2.2 + 0.8;
            $tagList[$key] = array(
                'count' => $value,
                'size' => number_format($size, 2, '.', ''),
            );
        }

        $data = array(
            'tags' => $tagList,
        );
        $this->pluginManager->executeHooks('render_tagcloud', $data, array('loggedin' => isLoggedIn()));

        foreach ($data as $key => $value) {
            $this->tpl->assign($key, $value);
        }

        $this->tpl->renderPage('tagcloud');
    }
}
