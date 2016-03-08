<?php

class Thumbnailer
{
    private static $SUPPORTED_DOMAINS = array(
        'youtube.com',
        'imgur.com'
    );

    private static $IMAGE_EXTENSIONS = array(
        'jpeg',
        'jpg',
        'png',
    );

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
     * @var string Instance's salt, used to sign thumbnail request.
     */
    private $salt;

    public function __construct($url, $cacheDir, $salt)
    {
        $this->url = $url;
        $this->cacheDir = $cacheDir;
        $this->salt = $salt;
    }

    public function thumbnail()
    {
        // Direct link to an image or supported domain.
        if (! $this->isSupported()) {
            return false;
        }
    }

    public function isSupported() {
        $lowerUrl = strtolower($url);
        foreach (self::$IMAGE_EXTENSIONS as $ext) {
            if(endsWith($lowerUrl, $ext))
        }
    }
}