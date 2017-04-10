<?php

namespace Shaarli\Links;

class Link
{
    /**
     * @var int Link ID
     */
    protected $id;

    /**
     * @var string Permalink identifier
     */
    protected $shortUrl;

    protected $url;

    protected $title;

    protected $description;

    /**
     * @var array
     */
    protected $tags;

    protected $created;

    protected $updated;

    /**
     * @var bool
     */
    protected $private;

    public function fromArrayMigration($data)
    {
        $this->id = $data['id'];
        $this->shortUrl = $data['shorturl'];
        $this->url = $data['url'];
        $this->title = $data['title'];
        $this->description = $data['description'];
        $this->created = $data['created'];
        if (! empty($data['updated'])) {
            $this->updated = $data['updated'];
        }
        $this->private = $data['private'];

        return $this;
    }

    public function validate()
    {
        // todo: check id
        if ($this->id === null || empty($this->shortUrl) || empty($this->created)) {
            throw new \Exception();
        }
        if (empty($this->url)) {
            $this->url = $this->shortUrl;
        }
        if (empty($this->title)) {
            $this->title = $this->url;
        }
    }

    /**
     * Get the Id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the ShortUrl.
     *
     * @return string
     */
    public function getShortUrl()
    {
        return $this->shortUrl;
    }

    /**
     * Get the Url.
     *
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get the Title.
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get the Description.
     *
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get the Created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get the Updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set the Id.
     *
     * @param mixed $id
     *
     * @return Link
     */
    public function setId($id)
    {
        $this->id = $id;
        $this->created = new \DateTime();
        $this->shortUrl = link_small_hash($this->created, $this->id);

        return $this;
    }

    /**
     * Set the Url.
     *
     * @param mixed $url
     *
     * @return Link
     */
    public function setUrl($url)
    {
        $url = trim($_POST['lf_url']);
        $allowedProtocols = ['http', 'https', 'ftp', 'magnet', 'javascript'];
        $protocol = substr($url, 0, strpos($url, '://'));
        if (! in_array($protocol, $allowedProtocols)) {
            $url = 'http://' . $url;
        }

        $this->url = $url;

        return $this;
    }

    /**
     * Set the Title.
     *
     * @param mixed $title
     *
     * @return Link
     */
    public function setTitle($title)
    {
        $this->title = trim($title);

        return $this;
    }

    /**
     * Set the Description.
     *
     * @param mixed $description
     *
     * @return Link
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the Updated.
     *
     * @param mixed $updated
     *
     * @return Link
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get the Private.
     *
     * @return bool
     */
    public function isPrivate()
    {
        return $this->private;
    }

    /**
     * Set the Private.
     *
     * @param bool $private
     *
     * @return Link
     */
    public function setPrivate($private)
    {
        $this->private = $private;

        return $this;
    }

    /**
     * Get the Tags.
     *
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set the Tags.
     *
     * @param array $tags
     *
     * @return Link
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    public function getTagsString()
    {
        return implode(' ', $this->tags);
    }

    public function setTagsString($tags)
    {
        // Remove multiple spaces.
        $tags = trim(preg_replace('/\s\s+/', ' ', $_POST['lf_tags']));
        // Remove first '-' char in tags.
        $tags = preg_replace('/(^| )\-/', '$1', $tags);
        // Remove duplicates.
        $tags = implode(' ', array_unique(explode(' ', $tags)));

        $this->tags = explode(' ', $tags);

        return $this;
    }
}
