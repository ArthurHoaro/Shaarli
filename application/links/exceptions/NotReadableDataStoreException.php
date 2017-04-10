<?php


namespace Shaarli\Links\Exceptions;


class NotReadableDataStoreException extends \Exception
{
    /**
     * @var string Data store file path
     */
    protected $dataStore;

    /**
     * NotReadableDataStore constructor.
     *
     * @param string $dataStore file path
     */
    public function __construct($dataStore)
    {
        $this->dataStore = $dataStore;
        $this->message = 'Couldn\'t load data from the data store file "'. $this->dataStore .'". '.
            'Your data might be corrupted, or your file isn\'t readable.';
    }
}
