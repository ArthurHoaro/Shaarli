<?php


namespace Shaarli\Links;


use Shaarli\Config\ConfigManager;
use Shaarli\Links\Exceptions\LinkNotFoundException;

/**
 * Class LinksService
 *
 * This is the entry point to manipulate the link DB.
 *
 * @package Shaarli\Db
 */
class LinksService
{
    /**
     * @var LinksArray instance
     */
    protected $links;

    /**
     * @var LinksIO instance
     */
    protected $linksIO;

    /**
     * @var LinksFilter
     */
    protected $linksFilter;

    /**
     * @var ConfigManager instance
     */
    protected $conf;

    /**
     * @var bool true for logged in users. Default value to retrieve private links.
     */
    protected $isLoggedIn;

    /**
     * LinksService constructor.
     *
     * @param ConfigManager $conf       instance
     * @param bool          $isLoggedIn true if the current user is logged in
     */
    public function __construct(ConfigManager $conf, $isLoggedIn)
    {
        $this->conf = $conf;
        $this->linksIO = new LinksIO($this->conf);
        $this->isLoggedIn = $isLoggedIn;

        // tmp => Updater
        $links = $this->linksIO->read();
        $this->links = new LinksArray();
        foreach ($links as $key => $link) {
            $this->links[$key] = (new Link())->fromArrayMigration($link);
        }
        $this->linksFilter = new LinksFilter($this->links);
    }

    /**
     * Find all link according to given parameters, ordered by given keys
     *
     * @param string $visibility all|public|private
     *
     * @return Link[] list of Link result
     */
    public function findAll($visibility)
    {
        return $this->linksFilter->filter(LinksFilter::$DEFAULT, null, false, $visibility);
    }

    /**
     * Find a link by hash
     *
     * @param string $hash
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function findByHash($hash)
    {
        $link = array_pop($this->linksFilter->filter(LinksFilter::$FILTER_HASH, $hash));
        if (! $this->isLoggedIn && $link->isPrivate()) {
            throw new \Exception('Not authorized');
        }

        return $link;
    }

    public function findAllTags($visibility = null)
    {
        // todo
        return [];
    }

    /**
     * Search links
     *
     * @param mixed  $request
     * @param string $visibility
     * @param bool   $caseSensitive
     *
     * @return Link[]
     */
    public function search($request, $visibility, $caseSensitive = false)
    {
        return $this->linksFilter->filter(
            LinksFilter::$FILTER_TAG | LinksFilter::$FILTER_TEXT,
            $request,
            $caseSensitive,
            $visibility
        );
    }

    /**
     * Get a single link by its ID.
     *
     * @param int    $id         Link ID
     * @param string $visibility all|public|private e.g. with public, accessing a private link will throw an exception
     *
     * @return Link
     *
     * @throws LinkNotFoundException
     * @throws \Exception
     */
    public function get($id, $visibility = null)
    {
        if (! isset($this->links[$id])) {
            throw new LinkNotFoundException();
        }

        if ($visibility === null) {
            $visibility = $this->isLoggedIn ? 'all' : 'public';
        }

        $link = $this->links[$id];
        if (($link->isPrivate() && $visibility != 'all' && $visibility != 'private')
            || (! $link->isPrivate() && $visibility != 'all' && $visibility != 'public')
        ) {
            throw new \Exception('Unauthorized');
        }

        return $link;
    }

    /**
     * Updates an existing link (depending on its ID).
     *
     * @param Link $link
     * @param bool $save Writes to the datastore if set to true
     *
     * @return Link Updated link
     *
     * @throws LinkNotFoundException
     * @throws \Exception
     */
    public function set($link, $save = true)
    {
        if ($this->isLoggedIn !== true) {
            throw new \Exception(t('You\'re not authorized to alter the datastore'));
        }
        if (! $link instanceof Link) {
            throw new \Exception(t('Provided data is invalid'));
        }
        if (! isset($this->links[$link->getId()])) {
            throw new LinkNotFoundException();
        }
        $link->validate();

        $link->setUpdated(new \DateTime());
        $this->links[$link->getId()] = $link;
        if ($save === true) {
            $this->linksIO->write($this->links);
        }
        return $this->links[$link->getId()];
    }

    /**
     * Adds a new link (the ID must be empty).
     *
     * @param Link $link
     * @param bool $save Writes to the datastore if set to true
     *
     * @return Link new link
     *
     * @throws \Exception
     */
    public function add($link, $save = true)
    {
        if ($this->isLoggedIn !== true) {
            throw new \Exception(t('You\'re not authorized to alter the datastore'));
        }
        if (! $link instanceof Link) {
            throw new \Exception(t('Provided data is invalid'));
        }
        if (! empty($link->getId())) {
            throw new \Exception(t('This links already exists'));
        }
        $link->setId($this->links->getNextId());
        $link->validate();

        $this->links[$link->getId()] = $link;
        if ($save === true) {
            $this->linksIO->write($this->links);
        }
        return $this->links[$link->getId()];
    }

    /**
     * Adds or updates a link depending on its ID:
     *   - a Link without ID will be added
     *   - a Link with an existing ID will be updated
     *
     * @param Link $link
     * @param bool $save
     *
     * @return Link
     *
     * @throws \Exception
     */
    public function addOrSet($link, $save = true)
    {
        if ($this->isLoggedIn !== true) {
            throw new \Exception(t('You\'re not authorized to alter the datastore'));
        }
        if (! $link instanceof Link) {
            throw new \Exception();
        }
        if (empty($link->getId())) {
            return $this->add($link, $save);
        }
        return $this->set($link, $save);
    }

    /**
     * Deletes a link.
     *
     * @param Link $link
     * @param bool $save
     *
     * @throws \Exception
     */
    public function remove($link, $save = true)
    {
        if ($this->isLoggedIn !== true) {
            throw new \Exception(t('You\'re not authorized to alter the datastore'));
        }
        if (! $link instanceof Link) {
            throw new \Exception(t('Provided data is invalid'));
        }
        if (! isset($this->links[$link->getId()])) {
            throw new \Exception();
        }

        unset($this->links[$link->getId()]);
        if ($save === true) {
            $this->linksIO->write($this->links);
        }
    }

    /**
     * Get a single link by its ID.
     *
     * @param int    $id         Link ID
     * @param string $visibility all|public|private e.g. with public, accessing a private link will throw an exception
     *
     * @return bool
     */
    public function exists($id, $visibility = null)
    {
        if (! isset($this->links[$id])) {
            return false;
        }

        if ($visibility === null) {
            $visibility = $this->isLoggedIn ? 'all' : 'public';
        }

        $link = $this->links[$id];
        if (($link->isPrivate() && $visibility != 'all' && $visibility != 'private')
            || (! $link->isPrivate() && $visibility != 'all' && $visibility != 'public')
        ) {
            return false;
        }

        return true;
    }
}
