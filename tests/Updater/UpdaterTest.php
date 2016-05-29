<?php

require_once 'application/config/ConfigManager.php';
require_once 'tests/Updater/DummyUpdater.php';

/**
 * Class UpdaterTest.
 * Runs unit tests against the Updater class.
 */
class UpdaterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array Configuration input set.
     */
    private static $configFields;

    /**
     * @var string Path to test datastore.
     */
    protected static $testDatastore = 'sandbox/datastore.php';

    /**
     * @var string Config file path (without extension).
     */
    protected static $configFile = 'tests/utils/config/configUpdater';

    /**
     * @var ConfigManager
     */
    protected $conf;

    /**
     * Executed before each test.
     */
    public function setUp()
    {
        self::$configFields = array(
            'login' => 'login',
            'hash' => 'hash',
            'salt' => 'salt',
            'timezone' => 'Europe/Paris',
            'title' => 'title',
            'titleLink' => 'titleLink',
            'redirector' => '',
            'disablesessionprotection' => false,
            'privateLinkByDefault' => false,
            'config' => array(
                'DATADIR' => 'tests/Updater',
                'PAGECACHE' => 'sandbox/pagecache',
                'config1' => 'config1data',
                'config2' => 'config2data',
            )
        );

        ConfigManager::$CONFIG_FILE = self::$configFile;
        $this->conf = ConfigManager::reset();
        $this->conf->reload();
        foreach (self::$configFields as $key => $value) {
            $this->conf->set($key, $value);
        }
        $this->conf->write(true);
    }

    /**
     * Executed after each test.
     *
     * @return void
     */
    public function tearDown()
    {
        if (is_file('tests/Updater/config.json')) {
            unlink('tests/Updater/config.json');
        }

        if (is_file(self::$configFields['config']['DATADIR'] . '/options.php')) {
            unlink(self::$configFields['config']['DATADIR'] . '/options.php');
        }

        if (is_file(self::$configFields['config']['DATADIR'] . '/updates.txt')) {
            unlink(self::$configFields['config']['DATADIR'] . '/updates.txt');
        }
    }

    /**
     * Test read_updates_file with an empty/missing file.
     */
    public function testReadEmptyUpdatesFile()
    {
        $this->assertEquals(array(), read_updates_file(''));
        $updatesFile = self::$configFields['config']['DATADIR'] . '/updates.txt';
        touch($updatesFile);
        $this->assertEquals(array(), read_updates_file($updatesFile));
    }

    /**
     * Test read/write updates file.
     */
    public function testReadWriteUpdatesFile()
    {
        $updatesFile = self::$configFields['config']['DATADIR'] . '/updates.txt';
        $updatesMethods = array('m1', 'm2', 'm3');

        write_updates_file($updatesFile, $updatesMethods);
        $readMethods = read_updates_file($updatesFile);
        $this->assertEquals($readMethods, $updatesMethods);

        // Update
        $updatesMethods[] = 'm4';
        write_updates_file($updatesFile, $updatesMethods);
        $readMethods = read_updates_file($updatesFile);
        $this->assertEquals($readMethods, $updatesMethods);
    }

    /**
     * Test errors in write_updates_file(): empty updates file.
     *
     * @expectedException              Exception
     * @expectedExceptionMessageRegExp /Updates file path is not set(.*)/
     */
    public function testWriteEmptyUpdatesFile()
    {
        write_updates_file('', array('test'));
    }

    /**
     * Test errors in write_updates_file(): not writable updates file.
     *
     * @expectedException              Exception
     * @expectedExceptionMessageRegExp /Unable to write(.*)/
     */
    public function testWriteUpdatesFileNotWritable()
    {
        $updatesFile = self::$configFields['config']['DATADIR'] . '/updates.txt';
        touch($updatesFile);
        chmod($updatesFile, 0444);
        @write_updates_file($updatesFile, array('test'));
    }

    /**
     * Test the update() method, with no update to run.
     *   1. Everything already run.
     *   2. User is logged out.
     */
    public function testNoUpdates()
    {
        $updates = array(
            'updateMethodDummy1',
            'updateMethodDummy2',
            'updateMethodDummy3',
            'updateMethodException',
        );
        $updater = new DummyUpdater($updates, array(), true);
        $this->assertEquals(array(), $updater->update());

        $updater = new DummyUpdater(array(), array(), false);
        $this->assertEquals(array(), $updater->update());
    }

    /**
     * Test the update() method, with all updates to run (except the failing one).
     */
    public function testUpdatesFirstTime()
    {
        $updates = array('updateMethodException',);
        $expectedUpdates = array(
            'updateMethodDummy1',
            'updateMethodDummy2',
            'updateMethodDummy3',
        );
        $updater = new DummyUpdater($updates, array(), true);
        $this->assertEquals($expectedUpdates, $updater->update());
    }

    /**
     * Test the update() method, only one update to run.
     */
    public function testOneUpdate()
    {
        $updates = array(
            'updateMethodDummy1',
            'updateMethodDummy3',
            'updateMethodException',
        );
        $expectedUpdate = array('updateMethodDummy2');

        $updater = new DummyUpdater($updates, array(), true);
        $this->assertEquals($expectedUpdate, $updater->update());
    }

    /**
     * Test Update failed.
     *
     * @expectedException UpdaterException
     */
    public function testUpdateFailed()
    {
        $updates = array(
            'updateMethodDummy1',
            'updateMethodDummy2',
            'updateMethodDummy3',
        );

        $updater = new DummyUpdater($updates, array(), true);
        $updater->update();
    }

    /**
     * Test update mergeDeprecatedConfig:
     *      1. init a config file.
     *      2. init a options.php file with update value.
     *      3. merge.
     *      4. check updated value in config file.
     */
    public function testUpdateMergeDeprecatedConfig()
    {
        // Use writeConfig to create a options.php
        ConfigManager::$CONFIG_FILE = 'tests/Updater/options';
        $this->conf->setConfigIO(new ConfigPhp());

        $invert = !$this->conf->get('privateLinkByDefault');
        $this->conf->set('privateLinkByDefault', $invert);
        $this->conf->write(true);

        $optionsFile = 'tests/Updater/options.php';
        $this->assertTrue(is_file($optionsFile));

        ConfigManager::$CONFIG_FILE = 'tests/Updater/config';

        // merge configs
        $updater = new Updater(array(), array(), true);
        // This writes a new config file in tests/Updater/config.php
        $updater->updateMethodMergeDeprecatedConfigFile();

        // make sure updated field is changed
        $this->conf->reload();
        $this->assertEquals($invert, $this->conf->get('privateLinkByDefault'));
        $this->assertFalse(is_file($optionsFile));
        // Delete the generated file.
        unlink($this->conf->getConfigFile());
    }

    /**
     * Test mergeDeprecatedConfig in without options file.
     */
    public function testMergeDeprecatedConfigNoFile()
    {
        $updater = new Updater(array(), array(), true);
        $updater->updateMethodMergeDeprecatedConfigFile();

        $this->assertEquals(self::$configFields['login'], $this->conf->get('login'));
    }

    /**
     * Test renameDashTags update method.
     */
    public function testRenameDashTags()
    {
        $refDB = new ReferenceLinkDB();
        $refDB->write(self::$testDatastore);
        $linkDB = new LinkDB(self::$testDatastore, true, false);
        $this->assertEmpty($linkDB->filterSearch(array('searchtags' => 'exclude')));
        $updater = new Updater(array(), $linkDB, true);
        $updater->updateMethodRenameDashTags();
        $this->assertNotEmpty($linkDB->filterSearch(array('searchtags' =>  'exclude')));
    }

    /**
     * Convert old PHP config file to JSON config.
     */
    public function testConfigToJson()
    {
        $configFile = 'tests/utils/config/configPhp';
        ConfigManager::$CONFIG_FILE = $configFile;
        $conf = ConfigManager::reset();

        // The ConfigIO is initialized with ConfigPhp.
        $this->assertTrue($conf->getConfigIO() instanceof ConfigPhp);

        $updater = new Updater(array(), array(), false);
        $done = $updater->updateMethodConfigToJson();
        $this->assertTrue($done);

        // The ConfigIO has been updated to ConfigJson.
        $this->assertTrue($conf->getConfigIO() instanceof ConfigJson);
        $this->assertTrue(file_exists($conf->getConfigFile()));

        // Check JSON config data.
        $conf->reload();
        $this->assertEquals('root', $conf->get('login'));
        $this->assertEquals('lala', $conf->get('redirector'));
        $this->assertEquals('data/datastore.php', $conf->get('config.DATASTORE'));
        $this->assertEquals('1', $conf->get('plugins.WALLABAG_VERSION'));

        rename($configFile . '.save.php', $configFile . '.php');
        unlink($conf->getConfigFile());
    }

    /**
     * Launch config conversion update with an existing JSON file => nothing to do.
     */
    public function testConfigToJsonNothingToDo()
    {
        $configFile = 'tests/utils/config/configUpdateDone';
        ConfigManager::$CONFIG_FILE = $configFile;
        $conf = ConfigManager::reset();
        $conf->reload();
        $filetime = filemtime($conf->getConfigFile());
        $updater = new Updater(array(), array(), false);
        $done = $updater->updateMethodConfigToJson();
        $this->assertTrue($done);
        $expected = filemtime($conf->getConfigFile());
        $this->assertEquals($expected, $filetime);
    }
}
