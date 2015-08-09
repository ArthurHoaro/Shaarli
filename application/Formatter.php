<?php
/**
 * Functions used to format data.
 */

/**
 * @param $description
 * @return mixed|string
 */
function description_to_html($description)
{
    $description = text_to_clickable($description);
    $description = add_hashtags($description);
    $description = keep_multiple_spaces($description);
    $description = nl2br($description);

    return $description;
}

/**
 * @param $description
 * @return mixed
 */
function add_hashtags($description)
{
    // Regex: start of line or space, followed by a '#' and anything except spaces and #.
    $regex = '/(?:^| |\n|\r\n)(#[^ \t\n\r#)]+)/';
    $matches = array();
    $descHtml = $description;

    if (!preg_match_all($regex, $description, $matches)) {
        return $descHtml;
    }

    foreach ($matches[1] as $value) {
        $hashtag = substr($value, 1);
        $hashtagHtml = '<a href="'. indexUrl() .'?addtag='. urlencode($hashtag) .'" class="hashtaglink">';
        $hashtagHtml .= $value;
        $hashtagHtml .= '</a>';
        $descHtml = str_replace($value, $hashtagHtml, $descHtml);
    }

    return $descHtml;
}

// In a string, converts URLs to clickable links.
// Function inspired from http://www.php.net/manual/en/function.preg-replace.php#85722
function text_to_clickable($url)
{
    $redir = empty($GLOBALS['redirector']) ? '' : $GLOBALS['redirector'];
    return preg_replace('!(((?:https?|ftp|file)://|apt:|magnet:)\S+[[:alnum:]]/?)!si','<a href="'.$redir.'$1" rel="nofollow">$1</a>',$url);
}

// This function inserts &nbsp; where relevant so that multiple spaces are properly displayed in HTML
// even in the absence of <pre>  (This is used in description to keep text formatting)
function keep_multiple_spaces($text)
{
    return str_replace('  ',' &nbsp;',$text);
}
