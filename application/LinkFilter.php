<?php

/**
 * Class LinkFilter.
 *
 * Perform search and filter operation on link data list.
 */
class LinkFilter
{
    /**
     * @var string permalinks.
     */
    public static $FILTER_HASH   = 'permalink';

    /**
     * @var string text search.
     */
    public static $FILTER_TEXT   = 'fulltext';

    /**
     * @var string tag filter.
     */
    public static $FILTER_TAG    = 'tags';

    /**
     * @var string filter by day.
     */
    public static $FILTER_DAY    = 'FILTER_DAY';

    /**
     * @var string Allowed characters for hashtags (regex syntax).
     */
    public static $HASHTAG_CHARS = '\p{Pc}\p{N}\p{L}\p{Mn}';

    /**
     * @var string CSS class name for search result highlight.
     */
    public static $HIGHLIGHT_CLASS = 'search-highlight';

    /**
     * @var LinkDB all available links.
     */
    private $links;

    /**
     * @param LinkDB $links initialization.
     */
    public function __construct($links)
    {
        $this->links = $links;
    }

    /**
     * Filter links according to parameters.
     *
     * @param string $type          Type of filter (eg. tags, permalink, etc.).
     * @param mixed  $request       Filter content.
     * @param bool   $casesensitive Optional: Perform case sensitive filter if true.
     * @param string $visibility    Optional: return only all/private/public links
     *
     * @return array filtered link list.
     */
    public function filter($type, $request, $casesensitive = false, $visibility = 'all')
    {
        if (! in_array($visibility, ['all', 'public', 'private'])) {
            $visibility = 'all';
        }

        switch($type) {
            case self::$FILTER_HASH:
                return $this->filterSmallHash($request);
            case self::$FILTER_TAG | self::$FILTER_TEXT:
                if (!empty($request)) {
                    $filtered = $this->links;
                    if (isset($request[0])) {
                        $filtered = $this->filterTags($request[0], $casesensitive, $visibility);
                    }
                    if (isset($request[1])) {
                        $lf = new LinkFilter($filtered);
                        $filtered = $lf->filterFulltext($request[1], $visibility);
                    }
                    return $filtered;
                }
                return $this->noFilter($visibility);
            case self::$FILTER_TEXT:
                return $this->filterFulltext($request, $visibility);
            case self::$FILTER_TAG:
                return $this->filterTags($request, $casesensitive, $visibility);
            case self::$FILTER_DAY:
                return $this->filterDay($request);
            default:
                return $this->noFilter($visibility);
        }
    }

    /**
     * Unknown filter, but handle private only.
     *
     * @param string $visibility Optional: return only all/private/public links
     *
     * @return array filtered links.
     */
    private function noFilter($visibility = 'all')
    {
        if ($visibility === 'all') {
            return $this->links;
        }

        $out = array();
        foreach ($this->links as $key => $value) {
            if ($value['private'] && $visibility === 'private') {
                $out[$key] = $value;
            } else if (! $value['private'] && $visibility === 'public') {
                $out[$key] = $value;
            }
        }

        return $out;
    }

    /**
     * Returns the shaare corresponding to a smallHash.
     *
     * @param string $smallHash permalink hash.
     *
     * @return array $filtered array containing permalink data.
     *
     * @throws LinkNotFoundException if the smallhash doesn't match any link.
     */
    private function filterSmallHash($smallHash)
    {
        $filtered = array();
        foreach ($this->links as $key => $l) {
            if ($smallHash == $l['shorturl']) {
                // Yes, this is ugly and slow
                $filtered[$key] = $l;
                return $filtered;
            }
        }

        if (empty($filtered)) {
            throw new LinkNotFoundException();
        }

        return $filtered;
    }

    /**
     * Returns the list of links corresponding to a full-text search
     *
     * Searches:
     *  - in the URLs, title and description;
     *  - are case-insensitive;
     *  - terms surrounded by quotes " are exact terms search.
     *  - terms starting with a dash - are excluded (except exact terms).
     *
     * Example:
     *    print_r($mydb->filterFulltext('hollandais'));
     *
     * mb_convert_case($val, MB_CASE_LOWER, 'UTF-8')
     *  - allows to perform searches on Unicode text
     *  - see https://github.com/shaarli/Shaarli/issues/75 for examples
     *
     * @param string $searchterms search query.
     * @param string $visibility Optional: return only all/private/public links.
     *
     * @return array search results.
     */
    private function filterFulltext($searchterms, $visibility = 'all')
    {
        if (empty($searchterms)) {
            return $this->noFilter($visibility);
        }

        $filtered = array();
        $search = mb_convert_case(html_entity_decode($searchterms), MB_CASE_LOWER, 'UTF-8');
        $exactRegex = '/"([^"]+)"/';
        // Retrieve exact search terms.
        preg_match_all($exactRegex, $search, $exactSearch);
        $exactSearch = array_values(array_filter($exactSearch[1]));

        // Remove exact search terms to get AND terms search.
        $explodedSearchAnd = explode(' ', trim(preg_replace($exactRegex, '', $search)));
        $explodedSearchAnd = array_values(array_filter($explodedSearchAnd));

        // Filter excluding terms and update andSearch.
        $excludeSearch = array();
        $andSearch = array();
        foreach ($explodedSearchAnd as $needle) {
            if ($needle[0] == '-' && strlen($needle) > 1) {
                $excludeSearch[] = substr($needle, 1);
            } else {
                $andSearch[] = $needle;
            }
        }

        $keys = array('title_display', 'description', 'url_display', 'tags');

        // Remove links no matching visibility
        $links = $this->noFilter($visibility);

        // Iterate over every stored link.
        foreach ($links as $id => $link) {
            // Convert all field to lowercase.
            $lowercaseLink = [];
            foreach ($keys as $key) {
                $lowercaseLink[$key] = mb_convert_case($link[$key], MB_CASE_LOWER, 'UTF-8');
            }

            // Exact search: will search for exact terms.
            if (self::searchAndHighlight($link, $lowercaseLink, $exactSearch, $keys, false) !== true) {
                continue;
            }
            // Normal full text search across
            if (self::searchAndHighlight($link, $lowercaseLink, $andSearch, $keys, false) !== true) {
                continue;
            }
            // Exclude search
            if (self::searchAndHighlight($link, $lowercaseLink, $excludeSearch, $keys, true) !== false) {
                continue;
            }
            $filtered[$id] = $link;
        }

        return $filtered;
    }

    /**
     * Returns the list of links associated with a given list of tags
     *
     * You can specify one or more tags, separated by space or a comma, e.g.
     *  print_r($mydb->filterTags('linux programming'));
     *
     * @param string $tags          list of tags separated by commas or blank spaces.
     * @param bool   $casesensitive ignore case if false.
     * @param string $visibility    Optional: return only all/private/public links.
     *
     * @return array filtered links.
     */
    public function filterTags($tags, $casesensitive = false, $visibility = 'all')
    {
        // Implode if array for clean up.
        $tags = is_array($tags) ? trim(implode(' ', $tags)) : $tags;
        if (empty($tags)) {
            return $this->noFilter($visibility);
        }

        $searchtags = self::tagsStrToArray($tags, $casesensitive);
        $filtered = array();
        if (empty($searchtags)) {
            return $filtered;
        }

        foreach ($this->links as $key => $link) {
            // ignore non private links when 'privatonly' is on.
            if ($visibility !== 'all') {
                if (! $link['private'] && $visibility === 'private') {
                    continue;
                } else if ($link['private'] && $visibility === 'public') {
                    continue;
                }
            }

            $linktags = self::tagsStrToArray($link['tags'], $casesensitive);

            $found = true;
            for ($i = 0 ; $i < count($searchtags) && $found; $i++) {
                // Exclusive search, quit if tag found.
                // Or, tag not found in the link, quit.
                if (($searchtags[$i][0] == '-'
                        && $this->searchTagAndHashTag(substr($searchtags[$i], 1), $linktags, $link['description']))
                    || ($searchtags[$i][0] != '-')
                        && ! $this->searchTagAndHashTag($searchtags[$i], $linktags, $link['description'])
                ) {
                    $found = false;
                }
            }

            if ($found) {
                $filtered[$key] = $link;
            }
        }
        return $filtered;
    }

    /**
     * Returns the list of articles for a given day, chronologically sorted
     *
     * Day must be in the form 'YYYYMMDD' (e.g. '20120125'), e.g.
     *  print_r($mydb->filterDay('20120125'));
     *
     * @param string $day day to filter.
     *
     * @return array all link matching given day.
     *
     * @throws Exception if date format is invalid.
     */
    public function filterDay($day)
    {
        if (! checkDateFormat('Ymd', $day)) {
            throw new Exception('Invalid date format');
        }

        $filtered = array();
        foreach ($this->links as $key => $l) {
            if ($l['created']->format('Ymd') == $day) {
                $filtered[$key] = $l;
            }
        }

        // sort by date ASC
        return array_reverse($filtered, true);
    }

    /**
     * Check if a tag is found in the taglist, or as an hashtag in the link description.
     *
     * @param string $tag         Tag to search.
     * @param array  $taglist     List of tags for the current link.
     * @param string $description Link description.
     *
     * @return bool True if found, false otherwise.
     */
    protected function searchTagAndHashTag($tag, $taglist, $description)
    {
        if (in_array($tag, $taglist)) {
            return true;
        }

        if (preg_match('/(^| )#'. $tag .'([^'. self::$HASHTAG_CHARS .']|$)/mui', $description) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Convert a list of tags (str) to an array. Also
     * - handle case sensitivity.
     * - accepts spaces commas as separator.
     *
     * @param string $tags          string containing a list of tags.
     * @param bool   $casesensitive will convert everything to lowercase if false.
     *
     * @return array filtered tags string.
     */
    public static function tagsStrToArray($tags, $casesensitive)
    {
        // We use UTF-8 conversion to handle various graphemes (i.e. cyrillic, or greek)
        $tagsOut = $casesensitive ? $tags : mb_convert_case($tags, MB_CASE_LOWER, 'UTF-8');
        $tagsOut = str_replace(',', ' ', $tagsOut);

        return preg_split('/\s+/', $tagsOut, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Search for terms in haystack keys and highlight found result.
     *
     * @param array $reference Link data reference to highlight
     * @param array $haystack  Link data where to search terms, should match reference data.
     *                         e.g. lowercase reference
     * @param array $terms     List of terms to search inside data.
     * @param array $keys      List of data key where to search.
     *                         We don't search in the date for example.
     * @param bool  $exclude   False for inclusive search or false exclusive search.
     *
     * @return bool true if the terms are found, false otherwise.
     */
    protected static function searchAndHighlight(&$reference, $haystack, $terms, $keys, $exclude) {
        $found = $exclude ? false : true;

        // Iterate over search keywords
        for ($i = 0; $i < count($terms); $i++) {
            $acrossFound = false;
            $j = 0;
            // Search string across all link fields. Stop anytime it's found.
            for (; $j < count($keys); ++$j) {
                $acrossFound = strpos($haystack[$keys[$j]], $terms[$i]);
                if ($acrossFound !== false) {
                    break;
                }
            }
            $found = $acrossFound;
            // Highlight found result in the actual link field, using found position.
            if ($found !== false) {
                if (! $exclude) {
                    $reference[$keys[$j]] = self::highlight($reference[$keys[$j]], $terms[$i], $found);
                }
                break;
            }
        }
        return $found !== false;
    }

    protected static function highlight($content, $found, $pos)
    {
        return substr_replace(
            substr_replace($content, '</span>', $pos + strlen($found), 0),
            '<span class="'. self::$HIGHLIGHT_CLASS .'">',
            $pos,
            0
        );
    }
}

class LinkNotFoundException extends Exception
{
    protected $message = 'The link you are trying to reach does not exist or has been deleted.';
}
