<?php

/**
 * Class LinkFilter.
 *
 * Perform search and filter operation on link data list.
 */
class LinkFilter {
    public static $FILTER_HASH   = 'permalink';
    public static $FILTER_TEXT   = 'fulltext';
    public static $FILTER_TAG    = 'tags';
    public static $FILTER_DAY    = 'FILTER_DAY';

    private $links;

    public function __construct($links)
    {
        $this->links = $links;
    }

    /**
     * Filter links according to parameters.
     *
     * @param string $type          Type of filter (eg. tags, permalink, etc.).
     * @param string $request       Filter content.
     * @param bool   $casesensitive Optional: Perform case sensitive filter if true.
     * @param bool   $privateonly   Optional: Only returns private links if true.
     *
     * @return array filtered link list.
     */
    public function filter($type, $request, $casesensitive = false, $privateonly = false) {
        switch($type) {
            case self::$FILTER_HASH:
                return $this->filterSmallHash($request);
                break;
            case self::$FILTER_TEXT:
                return $this->filterFulltext($request, $privateonly);
                break;
            case self::$FILTER_TAG:
                break;
            case self::$FILTER_DAY:
                break;
        }
    }

    /**
     * Returns the shaare corresponding to a smallHash.
     *
     * @param string $smallHash permalink hash.
     *
     * @return array $filtered array containing permalink data.
     */
    private function filterSmallHash($smallHash)
    {
        $filtered = array();
        foreach ($this->links as $l) {
            if ($smallHash == smallHash($l['linkdate'])) {
                // Yes, this is ugly and slow
                $filtered[$l['linkdate']] = $l;
                return $filtered;
            }
        }
        return $filtered;
    }

    /**
     * Returns the list of links corresponding to a full-text search
     *
     * Searches:
     *  - in the URLs, title and description;
     *  - are case-insensitive.
     *
     * Example:
     *    print_r($mydb->filterFulltext('hollandais'));
     *
     * mb_convert_case($val, MB_CASE_LOWER, 'UTF-8')
     *  - allows to perform searches on Unicode text
     *  - see https://github.com/shaarli/Shaarli/issues/75 for examples
     *
     * @param string $searchterms search query.
     * @param bool   $privateonly return only private links if true.
     *
     * @return array search results.
     */
    private function filterFulltext($searchterms, $privateonly = false)
    {
        // FIXME: accept double-quotes to search for a string "as is"?
        $filtered = array();
        $search = mb_convert_case($searchterms, MB_CASE_LOWER, 'UTF-8');
        $explodedSearch = explode(' ', trim($search));
        $keys = array('title', 'description', 'url', 'tags');

        // Iterate over every stored link.
        foreach ($this->links as $link) {
            $found = false;

            // ignore non private links when 'privatonly' is on.
            if ($link['private'] !== true && $privateonly === true) {
                continue;
            }

            // Iterate over searchable link fields.
            foreach ($keys as $key) {
                // Search full expression.
                if (strpos(mb_convert_case($link[$key], MB_CASE_LOWER, 'UTF-8'),
                        $search) !== false) {
                    $found = true;
                }
                // Search all single words individually.
                else {
                    $matchCount = 0;
                    foreach ($explodedSearch as $term) {
                        if (strpos(mb_convert_case($link[$key], MB_CASE_LOWER, 'UTF-8'),
                                $term) !== false) {
                            $matchCount++;
                        }
                    }
                    $found = ($matchCount == count($explodedSearch));
                }

                if ($found) {
                    break;
                }
            }

            if ($found) {
                $filtered[$link['linkdate']] = $link;
            }
        }

        krsort($filtered);
        return $filtered;
    }

    /**
     * Returns the list of links associated with a given list of tags
     *
     * You can specify one or more tags, separated by space or a comma, e.g.
     *  print_r($mydb->filterTags('linux programming'));
     */
    public function filterTags($tags, $casesensitive = false)
    {
        // We use UTF-8 conversion to handle various graphemes (i.e. cyrillic, or greek).
        $t = str_replace(
            ',', ' ',
            ($casesensitive ? $tags : mb_convert_case($tags, MB_CASE_LOWER, 'UTF-8'))
        );

        $searchtags = explode(' ', $t);
        $filtered = array();

        foreach ($this->_links as $l) {
            $linktags = explode(
                ' ',
                ($casesensitive ? $l['tags']:mb_convert_case($l['tags'], MB_CASE_LOWER, 'UTF-8'))
            );

            if (count(array_intersect($linktags, $searchtags)) == count($searchtags)) {
                $filtered[$l['linkdate']] = $l;
            }
        }
        krsort($filtered);
        return $filtered;
    }
} 