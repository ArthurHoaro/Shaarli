<?php

require_once 'autoload.php';

/**
 * Class ControllerTest
 *
 * Parent class for controller test classes.
 * It instantiate controllers parameters.
 */
abstract class ControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string path to test datastore.
     */
    public static $testDatastore = 'sandbox/datastore.php';

    /**
     * @var Controller
     */
    protected $controller;

    /**
     * @var PageBuilder
     */
    protected $pageBuilder;

    /**
     * @var ConfigManager
     */
    protected $conf;

    /**
     * @var PluginManager
     */
    protected $pluginManager;

    /**
     * @var LinkDB
     */
    protected $linkDB;

    /**
     * Called before every test.
     * Instantiate default objects used in the controller.
     */
    public function setUp()
    {
        $this->conf = new ConfigManager('tests/utils/config/configJson');
        $server = array(
            'SERVER_NAME' => 'domain.tld',
            'SCRIPT_NAME' => '',
            'QUERY_STRING' => '',
            'SERVER_PORT' => 80,
            'REQUEST_URI' => '',
            'REMOTE_ADDR' => '',
        );
        $this->pageBuilder = new PageBuilder($this->conf, $server, array(), false);
        $this->pluginManager = new PluginManager($this->conf);

        $refDB = new ReferenceLinkDB();
        $refDB->write(self::$testDatastore);

        $this->linkDB = new LinkDB(self::$testDatastore, true, false);
    }

    /**
     * Called after every test.
     * Delete the test datastore.
     */
    public function tearDown()
    {
        if (file_exists(self::$testDatastore)) {
            unlink(self::$testDatastore);
        }

        /*foreach (glob('tmp/*') as $file) {
            unlink($file);
        }*/
    }

    /**
     * Helper function checking if an array contains a string.
     * in_array() only checks if the needle is equal with an array element.
     *
     * @param string $needle   String to search.
     * @param array  $haystack Array to search in.
     *
     * @return bool true if the needle is found in the haystack, false otherwise.
     */
    public function arrayContains($needle, $haystack)
    {
        foreach ($haystack as $element) {
            if (strpos($element, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Test if a header has been set.
     *
     * @param string   $expected Expected header regex, without delimiters.
     * @param array    $headers  Headers set.
     *
     * @return bool true if the header has been set, false otherwise.
     */
    public function isHeaderSetRegex($expected, $headers)
    {
        foreach ($headers as $header) {
            if (preg_match('/'. $expected .'/', $header)) {
                return true;
            }
        }
        return false;
    }
}

/**
 * Overrides default getToken function.
 *
 * @param ConfigManager $conf Configuration Manager instance.
 *
 * @return string token.
 */
function getToken()
{
    return uniqid('', true);
}