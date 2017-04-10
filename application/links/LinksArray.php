<?php


namespace Shaarli\Links;


class LinksArray implements \Iterator, \Countable, \ArrayAccess
{
    /**
     * @var Link[]
     */
    protected $links;

    /**
     * @var array List of all links IDS mapped with their array offset.
     *            Map: id->offset.
     */
    protected $ids;

    /**
     * @var int Position in the $this->keys array (for the Iterator interface)
     */
    protected $position;

    /**
     * @var array List of offset keys (for the Iterator interface implementation)
     */
    protected $keys;

    /**
     * @var array List of all recorded URLs (key=url, value=link offset)
     *            for fast reserve search (url-->link offset)
     */
    protected $urls;

    /**
     * Countable - Counts elements of an object
     *
     * @return int Number of links
     */
    public function count()
    {
        return count($this->links);
    }

    /**
     * ArrayAccess - Assigns a value to the specified offset
     *
     * @param int  $offset Link ID
     * @param Link $value instance
     */
    public function offsetSet($offset, $value)
    {
        if ($value->getId() === null || empty($value->getUrl())) {
            die('Internal Error: A link should always have an id and URL.');
        }
        if (($offset !== null && ! is_int($offset)) || ! is_int($value->getId())) {
            die('You must specify an integer as a key.');
        }
        if ($offset !== null && $offset !== $value->getId()) {
            die('Array offset and link ID must be equal.');
        }

        // If the link exists, we reuse the real offset, otherwise new entry
        $existing = $this->getLinkOffset($offset);
        if ($existing !== null) {
            $offset = $existing;
        } else {
            $offset = count($this->links);
        }
        $this->links[$offset] = $value;
        $this->urls[$value->getUrl()] = $offset;
        $this->ids[$value->getId()] = $offset;
    }

    /**
     * ArrayAccess - Whether or not an offset exists
     *
     * @param int $offset Link ID
     *
     * @return bool true if it exists, false otherwise
     */
    public function offsetExists($offset)
    {
        return array_key_exists($this->getLinkOffset($offset), $this->links);
    }

    /**
     * ArrayAccess - Unsets an offset
     *
     * @param int $offset Link ID
     */
    public function offsetUnset($offset)
    {
        $realOffset = $this->getLinkOffset($offset);
        $url = $this->links[$realOffset]['url'];
        unset($this->urls[$url]);
        unset($this->ids[$realOffset]);
        unset($this->links[$realOffset]);
    }

    /**
     * ArrayAccess - Returns the value at specified offset
     *
     * @param int $offset Link ID
     *
     * @return Link|null The Link if found, null otherwise
     */
    public function offsetGet($offset)
    {
        $realOffset = $this->getLinkOffset($offset);
        return isset($this->links[$realOffset]) ? $this->links[$realOffset] : null;
    }

    /**
     * Iterator - Returns the current element
     *
     * @return Link corresponding to the current position
     */
    public function current()
    {
        return $this[$this->keys[$this->position]];
    }

    /**
     * Iterator - Returns the key of the current element
     *
     * @return int Link ID corresponding to the current position
     */
    public function key()
    {
        return $this->keys[$this->position];
    }

    /**
     * Iterator - Moves forward to next element
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Iterator - Rewinds the Iterator to the first element
     *
     * Entries are sorted by date (latest first)
     */
    public function rewind()
    {
        $this->keys = array_keys($this->ids);
        $this->position = 0;
    }

    /**
     * Iterator - Checks if current position is valid
     *
     * @return bool true if the current Link ID exists, false otherwise
     */
    public function valid()
    {
        return isset($this->keys[$this->position]);
    }

    /**
     * Returns a link offset in links array from its unique ID.
     *
     * @param int $id Persistent ID of a link.
     *
     * @return int Real offset in local array, or null if doesn't exist.
     */
    protected function getLinkOffset($id)
    {
        if (isset($this->ids[$id])) {
            return $this->ids[$id];
        }
        return null;
    }

    /**
     * Return the next key for link creation.
     * E.g. If the last ID is 597, the next will be 598.
     *
     * @return int next ID.
     */
    public function getNextId()
    {
        if (!empty($this->ids)) {
            return max(array_keys($this->ids)) + 1;
        }
        return 0;
    }
}
