<?php

/**
 * Class Thumbnail.
 */
class Thumbnail {

    /**
     * @var string URL from where we want a thumbnail.
     */
    private $url;

    /**
     * @var string Local cache directory path.
     * Can be disabled with an empty value (it disables thumbnail support).
     */
    private $cacheDir;

    /**
     * @var string Local cached thumbnail filename.
     */
    private $localFilename;

    /**
     * @var string Local cached blank filename.
     * If present, thumbnail couldn't be generated.
     */
    private $localBlankName;

    /**
     * @var string Instance's salt, used to sign thumbnail request.
     */
    private $salt;

    /**
     * Object constructor.
     *
     * @param $url      string URL from where we want a thumbnail.
     * @param $cacheDir string Local cache directory path.
     * @param $salt     string Instance salt.
     */
    public function __construct($url, $cacheDir, $salt) {
        $this->url = $url;
        $this->cacheDir = add_trailing_slash($cacheDir);
        $this->salt = $salt;
        $this->localFilename = hash('sha1', $this->url) .'.jpg';
        $this->localBlankName = hash('sha1', $this->url) .'.gif';
    }

    /**
     * Check that thumbnails are available:
     *   - PHP GD is enabled.
     *   - The cache dir exists and is writable.
     *
     * @return bool true if thumbnails can be generated, false otherwise.
     */
    public function isThumbnailAvailable() {
        return extension_loaded('gd')
            && function_exists('imagecreatefromjpeg')
            && !empty($this->cacheDir)
            && is_dir($this->cacheDir)
            && is_writable($this->cacheDir);
    }

    /**
     * Compute the thumbnail for a link.
     *
     * With a link to the original URL.
     * Understands various services (youtube.com...)
     * Input: $url = URL for which the thumbnail must be found.
     *        $href = if provided, this URL will be followed instead of $url
     * Returns an associative array with thumbnail attributes (src,href,width,height,style,alt)
     * Some of them may be missing.
     * Return an empty array if no thumbnail available.
     */
    public function computeThumbnail()
    {
        if (! $this->isThumbnailAvailable()) {
            return false;
        }

        // For most hosts, the URL of the thumbnail can be easily deduced from the URL of the link.
        // (e.g. http://www.youtube.com/watch?v=spVypYk4kto --->  http://img.youtube.com/vi/spVypYk4kto/default.jpg )
        //                                     ^^^^^^^^^^^                                 ^^^^^^^^^^^
        $domain = parse_url($this->url, PHP_URL_HOST);
        if ($domain=='youtube.com' || $domain=='www.youtube.com') {
            parse_str(parse_url($this->url, PHP_URL_QUERY), $params); // Extract video ID and get thumbnail
            if (! empty($params['v'])) {
                return array(
                    'src' => 'https://img.youtube.com/vi/'. $params['v'] .'/default.jpg',
                    'href' => $this->url,
                    'width' => '120',
                    'height' => '90',
                    'alt' => 'YouTube thumbnail'
                );
            }
        }
        // Youtube short links
        if ($domain == 'youtu.be') {
            $path = parse_url($this->url,PHP_URL_PATH);
            return array(
                'src' => 'https://img.youtube.com/vi'. $path .'/default.jpg',
                'href' => $this->url, 'width'=>'120',
                'height' => '90',
                'alt' => 'YouTube thumbnail'
            );
        }
        // pix.toile-libre.org image hosting
        if ($domain == 'pix.toile-libre.org') {
            parse_str(parse_url($this->url, PHP_URL_QUERY), $params); // Extract image filename.
            if (!empty($params) && !empty($params['img'])) {
                return array(
                    'src' => 'http://pix.toile-libre.org/upload/thumb/'.urlencode($params['img']),
                    'href' => $this->url,
                    'style' => 'max-width:120px; max-height:150px',
                    'alt' => 'pix.toile-libre.org thumbnail'
                );
            }
        }
        if ($domain == 'imgur.com') {
            $path = parse_url($this->url, PHP_URL_PATH);
            // Thumbnails for albums are not available.
            if (startsWith($path, '/a/')) {
                return false;
            }
            if (startsWith($path, '/r/')) {
                return array(
                    'src' => 'https://i.imgur.com/'. basename($path) .'s.jpg',
                    'href' => $this->url,
                    'width' => '90',
                    'height' => '90',
                    'alt' => 'imgur.com thumbnail'
                );
            }
            if (startsWith($path, '/gallery/')) {
                return array(
                    'src' => 'https://i.imgur.com'. substr($path,8) .'s.jpg',
                    'href' => $this->url,
                    'width' => '90',
                    'height' => '90',
                    'alt' => 'imgur.com thumbnail'
                );
            }

            if (substr_count($path, '/') == 1) {
                return array(
                    'src' => 'https://i.imgur.com/'. substr($path,1) .'s.jpg',
                    'href' => $this->url,
                    'width' => '90',
                    'height' => '90',
                    'alt' => 'imgur.com thumbnail'
                );
            }
        }
        if ($domain == 'i.imgur.com')
        {
            $pi = pathinfo(parse_url($this->url, PHP_URL_PATH));
            if (! empty($pi['filename'])) {
                return array(
                    'src' => 'https://i.imgur.com/'. $pi['filename'] .'s.jpg',
                    'href' => $this->url,
                    'width' => '90',
                    'height' => '90',
                    'alt' => 'imgur.com thumbnail'
                );
            }
        }
        if ($domain == 'dailymotion.com' || $domain == 'www.dailymotion.com') {
            if (strpos($this->url, 'dailymotion.com/video/') !== false) {
                $thumburl = str_replace('dailymotion.com/video/', 'dailymotion.com/thumbnail/video/', $this->url);
                return array(
                    'src' => $thumburl,
                    'href' => $this->url,
                    'width' => '120',
                    'style' => 'height:auto;',
                    'alt' => 'DailyMotion thumbnail'
                );
            }
        }
        if (endsWith($domain, '.imageshack.us')) {
            $ext = strtolower(pathinfo($this->url, PATHINFO_EXTENSION));
            if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif') {
                $thumburl = substr($this->url, 0, strlen($this->url) - strlen($ext)) .'th.'. $ext;
                return array(
                    'src' => $thumburl,
                    'href' => $this->url,
                    'width' => '120',
                    'style' => 'height:auto;',
                    'alt' => 'imageshack.us thumbnail'
                );
            }
        }

        // Some other hosts are SLOW AS HELL and usually require an extra HTTP request to get the thumbnail URL.
        // So we deport the thumbnail generation in order not to slow down page generation
        // (and we also cache the thumbnail)

        if ($domain=='flickr.com' || endsWith($domain,'.flickr.com')
            || $domain=='vimeo.com'
            || $domain=='ted.com' || endsWith($domain,'.ted.com')
            || $domain=='xkcd.com' || endsWith($domain,'.xkcd.com')
        )
        {
            if ($domain == 'vimeo.com') {
                $path = parse_url($this->url, PHP_URL_PATH);
                // Make sure this vimeo URL points to a video (/xxx... where xxx is numeric)
                if (! preg_match('!/\d+.+?!', $path)) {
                    return false; // This is not a single video URL.
                }
            }
            else if ($domain == 'xkcd.com' || endsWith($domain, '.xkcd.com')) {
                $path = parse_url($this->url, PHP_URL_PATH);
                // Make sure this URL points to a single comic (/xxx... where xxx is numeric)
                if (!preg_match('!/\d+.+?!', $path)) {
                    return array();
                }
            }
            else if ($domain == 'ted.com' || endsWith($domain, '.ted.com')) {
                $path = parse_url($this->url, PHP_URL_PATH);
                // Make sure this TED URL points to a video (/talks/...)
                if ('/talks/' !== substr($path, 0, 7)) {
                    return array(); // This is not a single video URL.
                }
            }
            // We use the salt to sign data (it's random, secret, and specific to each installation)
            $sign = hash_hmac('sha256', $this->url, $this->salt);
            return array(
                'src' => index_url($_SERVER) .'?do=genthumbnail&hmac='. $sign .'&url='. urlencode($this->url),
                'href' => $this->url,
                'width' => '120',
                'style' => 'height:auto;',
                'alt' => 'thumbnail'
            );
        }

        // For all other, we try to make a thumbnail of links ending with .jpg/jpeg/png/gif
        // Technically speaking, we should download ALL links and check their Content-Type to see if they are images.
        // But using the extension will do.
        $ext = strtolower(pathinfo($this->url, PATHINFO_EXTENSION));
        if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif') {
            // We use the salt to sign data (it's random, secret, and specific to each installation)
            $sign = hash_hmac('sha256', $this->url, $this->salt);
            return array(
                'src' => index_url($_SERVER) .'?do=genthumbnail&hmac='. $sign .'&url='. urlencode($this->url),
                'href' => $this->url,
                'width' => '120',
                'style' => 'height:auto;',
                'alt'=>'thumbnail'
            );
        }
        // No thumbnail.
        return false;
    }

    /**
     * Returns the HTML code to display a thumbnail for a link
     * with a link to the original URL.
     * Understands various services (youtube.com...)
     * Input: $url = URL for which the thumbnail must be found.
     *        $href = if provided, this URL will be followed instead of $url
     * Returns '' if no thumbnail available.
     */
    public function thumbnail()
    {
        if (! $this->isThumbnailAvailable()) {
            return false;
        }

        $t = $this->computeThumbnail();
        // No thumbnail for this URL.
        if (empty($t)) {
            return false;
        }

        $html = '<a href="'. escape($t['href']) .'"><img src="'. escape($t['src']) .'"';
        $html .= (!empty($t['width']))  ? ' width="'.  escape($t['width'])  .'"' : '';
        $html .= (!empty($t['height'])) ? ' height="'. escape($t['height']) .'"' : '';
        $html .= (!empty($t['style']))  ? ' style="'.  escape($t['style'])  .'"' : '';
        $html .= (!empty($t['alt']))    ? ' alt="'.    escape($t['alt'])    .'"' : '';
        $html .= '></a>';
        return $html;
    }

    /**
     * Returns the HTML code to display a thumbnail for a link
     * for the picture wall (using lazy image loading)
     * Understands various services (youtube.com...)
     * Input: $url = URL for which the thumbnail must be found.
     *        $href = if provided, this URL will be followed instead of $url
     * Returns '' if no thumbnail available.
     */
    public function lazyThumbnail()
    {
        if (! $this->isThumbnailAvailable()) {
            return false;
        }

        $t = $this->computeThumbnail();
        // No thumbnail for this URL.
        if (empty($t)) {
            return false;
        }

        $html = '<a href="'. escape($t['href']) .'">';

        $imgParams = '';
        $imgParams .= (!empty($t['width']))  ? ' width="'.  escape($t['width'])  .'"' : '';
        $imgParams .= (!empty($t['height'])) ? ' height="'. escape($t['height']) .'"' : '';
        $imgParams .= (!empty($t['style']))  ? ' style="'.  escape($t['style'])  .'"' : '';
        $imgParams .= (!empty($t['alt']))    ? ' alt="'.    escape($t['alt'])    .'"' : '';

        // Lazy image
        $html .= '<img class="b-lazy" src="#" data-src="'. escape($t['src']) .'"'. $imgParams .'>';
        // No-JavaScript fallback.
        $html .= '<noscript><img src="'. escape($t['src']) .'"'. $imgParams .'></noscript>';
        $html .= '</a>';

        return $html;
    }

    /**
     * Because some f*cking services like flickr require an extra HTTP request to get the thumbnail URL,
     * I have deported the thumbnail URL code generation here, otherwise this would slow down page generation.
     * The following function takes the URL a link (e.g. a flickr page) and return the proper thumbnail.
     * This function is called by passing the URL:
     *      http://mywebsite.com/shaarli/?do=genthumbnail&hmac=[HMAC]&url=[URL]
     *      [URL] is the URL of the link (e.g. a flickr page)
     *      [HMAC] is the signature for the [URL] (so that these URL cannot be forged).
     * The function below will fetch the image from the webservice and store it in the cache.
     */
    public function genThumbnail($signature)
    {
        if (! $this->isThumbnailAvailable()) {
            return false;
        }

        // Make sure the parameters in the URL were generated by us.
        $calculatedSignature = hash_hmac('sha256', $this->url, $this->salt);
        if ($calculatedSignature !== $signature) {
            die('Naughty boy!');
        }

        // Let's see if we don't already have the image for this URL in the cache.
        // We have the thumbnail, just serve it:
        if (is_file($this->cacheDir . $this->localFilename)) {
            $this->render();
            return false;
        }
        // We may also serve a blank image (if service did not respond)
        if (is_file($this->cacheDir . $this->localBlankName))
        {
            header('Content-Type: image/gif');
            echo file_get_contents($this->cacheDir . $this->localBlankName);
            return false;
        }

        // Otherwise, generate the thumbnail.
        $domain = parse_url($this->url, PHP_URL_HOST);

        if ($domain == 'flickr.com' || endsWith($domain, '.flickr.com')) {
            // Crude replacement to handle new flickr domain policy (They prefer www. now)
            $computedUrl = str_replace('http://flickr.com/', 'http://www.flickr.com/', $this->url);

            // Is this a link to an image, or to a flickr page ?
            $imageurl = '';
            if (endswith(parse_url($computedUrl, PHP_URL_PATH), '.jpg')) {
                // This is a direct link to an image. e.g. http://farm1.staticflickr.com/5/5921913_ac83ed27bd_o.jpg
                preg_match('!(http://farm\d+\.staticflickr\.com/\d+/\d+_\w+_)\w.jpg!', $computedUrl, $matches);
                if (! empty($matches[1])) {
                    $imageurl = $matches[1] .'m.jpg';
                }
            }
            // This is a flickr page (html)
            else {
                // Get the flickr html page.
                list($headers, $content) = get_http_response($computedUrl, 20);
                if (strpos($headers[0], '200 OK') !== false)
                {
                    // flickr now nicely provides the URL of the thumbnail in each flickr page.
                    preg_match('!<link rel=\"image_src\" href=\"(.+?)\"!', $content, $matches);
                    if (! empty($matches[1])) {
                        $imageurl = $matches[1];
                    }

                    // In albums (and some other pages), the link rel="image_src" is not provided,
                    // but flickr provides:
                    // <meta property="og:image" content="http://farm4.staticflickr.com/3398/3239339068_25d13535ff_z.jpg" />
                    if ($imageurl == '')
                    {
                        preg_match('!<meta property=\"og:image\" content=\"(.+?)\"!', $content, $matches);
                        if (! empty($matches[1])) {
                            $imageurl = $matches[1];
                        }
                    }
                }
            }

            // Let's download the image.
            if ($imageurl != '')
            {
                // Image is 240x120, so 10 seconds to download should be enough.
                list($headers, $content) = get_http_response($imageurl, 10);
                if (strpos($headers[0], '200 OK') !== false) {
                    // Save image to cache.
                    file_put_contents($this->cacheDir . $this->localFilename, $content);
                    $this->render($content);
                    return true;
                }
            }
        }
        elseif ($domain == 'vimeo.com' ) {
            // This is more complex: we have to perform a HTTP request, then parse the result.
            // Maybe we should deport this to JavaScript ?
            // Example: http://stackoverflow.com/questions/1361149/get-img-thumbnails-from-vimeo/4285098#4285098
            $vid = substr(parse_url($this->url, PHP_URL_PATH), 1);
            list($headers, $content) = get_http_response('https://vimeo.com/api/v2/video/'. escape($vid) .'.php', 5);
            if (strpos($headers[0], '200 OK') !== false) {
                $t = unserialize($content);
                $imageurl = $t[0]['thumbnail_medium'];
                // Then we download the image and serve it to our client.
                list($headers, $content) = get_http_response($imageurl, 10);
                if (strpos($headers[0], '200 OK') !== false) {
                    // Save image to cache.
                    file_put_contents($this->cacheDir . $this->localFilename, $content);
                    header('Content-Type: image/jpeg');
                    echo $content;
                    return true;
                }
            }
        }
        elseif ($domain == 'ted.com' || endsWith($domain, '.ted.com'))
        {
            // The thumbnail for TED talks is located in the <link rel="image_src" [...]> tag on that page
            // http://www.ted.com/talks/mikko_hypponen_fighting_viruses_defending_the_net.html
            // <link rel="image_src" href="http://images.ted.com/images/ted/28bced335898ba54d4441809c5b1112ffaf36781_389x292.jpg" />
            list($headers, $content) = get_http_response($this->url, 5);
            if (strpos($headers[0], '200 OK') !== false) {
                // Extract the link to the thumbnail
                preg_match('!link rel="image_src" href="(http://images.ted.com/images/ted/.+_\d+x\d+\.jpg)"!', $content, $matches);
                // Let's download the image.
                if (! empty($matches[1]))
                {
                    $imageurl = $matches[1];
                    // No control on image size, so wait long enough
                    list($headers, $content) = get_http_response($imageurl, 20);
                    if (strpos($headers[0], '200 OK') !== false) {
                        $filepath = $this->cacheDir . $this->localFilename;
                        file_put_contents($filepath, $content); // Save image to cache.
                        if ($this->resizeImage($filepath)) {
                            header('Content-Type: image/jpeg');
                            echo file_get_contents($filepath);
                            return true;
                        }
                    }
                }
            }
        }
        elseif ($domain == 'xkcd.com' || endsWith($domain, '.xkcd.com')) {
            // There is no thumbnail available for xkcd comics, so download the whole image and resize it.
            // http://xkcd.com/327/
            // <img src="http://imgs.xkcd.com/comics/exploits_of_a_mom.png" title="<BLABLA>" alt="<BLABLA>" />
            list($headers, $content) = get_http_response($this->url, 5);
            if (strpos($headers[0], '200 OK') !== false) {
                // Extract the link to the thumbnail
                preg_match('!<img src="(http://imgs.xkcd.com/comics/.*)" title="[^s]!', $content, $matches);
                if (! empty($matches[1])) {
                    // Let's download the image.
                    $imageurl = $matches[1];
                    // No control on image size, so wait long enough
                    list($headers, $content) = get_http_response($imageurl, 20);
                    if (strpos($headers[0], '200 OK') !== false) {
                        $filepath = $this->cacheDir . $this->localFilename;
                        // Save image to cache.
                        file_put_contents($filepath, $content);
                        if ($this->resizeImage($filepath)) {
                            header('Content-Type: image/jpeg');
                            echo file_get_contents($filepath);
                            return true;
                        }
                    }
                }
            }
        }
        else {
            // For all other domains, we try to download the image and make a thumbnail.
            // We allow 30 seconds max to download (and downloads are limited to 4 Mb)
            list($headers, $content) = get_http_response($this->url, 30);
            if (strpos($headers[0], '200 OK') !== false) {
                $filepath = $this->cacheDir . $this->localFilename;
                // Save image to cache.
                file_put_contents($filepath, $content);
                if ($this->resizeImage($filepath)) {
                    header('Content-Type: image/jpeg');
                    echo file_get_contents($filepath);
                    return true;
                }
            }
        }

        // Otherwise, return an empty image (8x8 transparent gif)
        $blankgif = base64_decode('R0lGODlhCAAIAIAAAP///////yH5BAEKAAEALAAAAAAIAAgAAAIHjI+py+1dAAA7');
        // Also put something in cache so that this URL is not requested twice.
        file_put_contents($this->cacheDir . $this->localBlankName, $blankgif);
        header('Content-Type: image/gif');
        echo $blankgif;
        return false;
    }

    // Make a thumbnail of the image (to width: 120 pixels)
    // Returns true if success, false otherwise.
    public function resizeImage($filepath)
    {
        if (! $this->isThumbnailAvailable()) {
            return false;
        }

        // GD not present: no thumbnail possible.
        if (!function_exists('imagecreatefromjpeg')) {
            return false;
        }

        // Trick: some stupid people rename GIF as JPEG... or else.
        // So we really try to open each image type whatever the extension is.
        // Read first 256 bytes and try to sniff file type.
        $header = file_get_contents($filepath, false, NULL, 0, 256);
        $image = false;
        $typeFound = strpos($header, 'GIF8');
        if ($typeFound !== false && $typeFound == 0) {
            // Well this is crude, but it should be enough.
            $image = imagecreatefromgif($filepath);
        }
        $typeFound = strpos($header, 'PNG');
        if ($typeFound !== false && $typeFound == 1) {
            $image = imagecreatefrompng($filepath);
        }
        $typeFound = strpos($header,'JFIF');
        if ($typeFound !== false) {
            $image = imagecreatefromjpeg($filepath);
        }
        // Unable to open image (corrupted or not an image)
        if (! $image) {
            return false;
        }
        $width = imagesx($image);
        $height = imagesy($image);
        $ystart = 0;
        $yheight = $height;
        if ($height > $width) {
            $ystart = ($height/2) - ($width/2);
            $yheight = $width/2;
        }
        $finalWidth = 120; // Desired width
        // Compute new width/height, but maximum 120 pixels height.
        $finalHeight = min(floor(($height*$finalWidth)/$width), 120);
        // Resize image:
        $im2 = imagecreatetruecolor($finalWidth, $finalHeight);
        imagecopyresampled($im2, $image, 0, 0, 0, $ystart, $finalWidth, $finalHeight, $width, $yheight);
        imageinterlace($im2, true); // For progressive JPEG.
        $tempname = $filepath .'_TEMP.jpg';
        imagejpeg($im2, $tempname, 90);
        imagedestroy($image);
        imagedestroy($im2);
        unlink($filepath);
        rename($tempname, $filepath);  // Overwrite original picture with thumbnail.
        return true;
    }

    private function render($content = false, $type = 'jpeg')
    {
        if ($content === false) {
            $content = file_get_contents($this->cacheDir . $this->localFilename);
        }

        header('Content-Type: image/'. $type);
        // Allow clients to cache thumbnails, expire after 7 days
        header('Cache-Control: must-revalidate');
        header_remove('Pragma');
        header('Expires: ' . gmdate(DATE_RFC1123, time() + 7*24*60*60));
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])
            && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= filemtime($this->cacheDir . $this->localFilename)) {
            header('HTTP/1.0 304 Not Modified');
        } else {
            header('Last-Modified: ' . date(DATE_RFC1123, filemtime($this->cacheDir . $this->localFilename)));
            header('Content-Length: ' . filesize($this->cacheDir . $this->localFilename));
            echo $content;
        }
    }
}
