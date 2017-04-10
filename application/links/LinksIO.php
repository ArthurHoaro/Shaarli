<?php


namespace Shaarli\Links;


use Shaarli\Config\ConfigManager;
use Shaarli\Links\Exceptions\NotReadableDataStoreException;

class LinksIO
{
    /**
     * @var string Datastore file path
     */
    protected $datastore;

    /**
     * @var ConfigManager instance
     */
    protected $conf;

    /**
     * string Datastore PHP prefix
     */
    protected static $phpPrefix = '<?php /* ';

    /**
     * string Datastore PHP suffix
     */
    protected static $phpSuffix = ' */ ?>';

    /**
     * LinksIO constructor.
     *
     * @param ConfigManager $conf instance
     */
    public function __construct($conf)
    {
        $this->conf = $conf;
        $this->datastore = $conf->get('resource.datastore');
    }

    /**
     * Reads database from disk to memory
     *
     * @return LinksArray instance
     *
     * @throws NotReadableDataStoreException Data couldn't be loaded
     */
    public function read()
    {
        // Note that gzinflate is faster than gzuncompress.
        // See: http://www.php.net/manual/en/function.gzdeflate.php#96439
        if (file_exists($this->datastore)) {
            $links = unserialize(gzinflate(base64_decode(
                substr(file_get_contents($this->datastore),
                    strlen(self::$phpPrefix), -strlen(self::$phpSuffix)))));
        }

        if (empty($links)) {
            throw new NotReadableDataStoreException($this->datastore);
        }

        return $links;
    }

    /**
     * Saves the database from memory to disk
     *
     * @param LinksArray $links instance.
     *
     * @throws \IOException the datastore is not writable
     */
    public function write($links)
    {
        if (is_file($this->datastore) && !is_writeable($this->datastore)) {
            // The datastore exists but is not writeable
            throw new \IOException($this->datastore);
        } else if (!is_file($this->datastore) && !is_writeable(dirname($this->datastore))) {
            // The datastore does not exist and its parent directory is not writeable
            throw new \IOException(dirname($this->datastore));
        }

        file_put_contents(
            $this->datastore,
            self::$phpPrefix.base64_encode(gzdeflate(serialize($links))).self::$phpSuffix
        );

        invalidateCaches($this->conf->get('resource.page_cache'));
    }
}
