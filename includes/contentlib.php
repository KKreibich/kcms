<?php
//Include Config & Libs
include($_SERVER["DOCUMENT_ROOT"] . "/includes/dblib.php");

class ContentItem
{
    private $id;
    private $url;
    private $title;
    private $subtitle;
    private $content;
    private $image;
    private $created;
    private $published;
    private $static;
    private $showdate;

    /**
     * Init the content-object
     * @param int $id The ID of the content object
     * @param string $url the end of the url for the page
     * @param string $title the title of the page
     * @param string $subtitle the subtitle of the page
     * @param string $content_html the content of the page (html support)
     * @param int $image the mediaID of the image
     * @param int $created timestamp, when the page was created / published
     * @param bool $published published
     * @param bool $static static
     * @param bool $showdate show date
     */
    function __construct(
        int $id,
        string $url,
        string $title,
        string $subtitle,
        string $content,
        int $image,
        int $created,
        bool $published,
        bool $static,
        bool $showdate
    ) {
        $this->id = $id;
        $this->url = $url;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->content = $content;
        $this->image = $image;
        $this->created = $created;
        $this->published = $published;
        $this->static = $static;
        $this->showdate = $showdate;
    }
    //* Write functions
    /**
     * Set new Data for the Object
     * @param string $url the end of the url for the page
     * @param string $title the title of the page
     * @param string $subtitle the subtitle of the page
     * @param string $content_html the content of the page (html support)
     * @param int $image the mediaID of the image
     * @param int $created timestamp, when the page was created / published
     * @param bool $published published
     * @param bool $static static
     * @param bool $showdate show date
     */
    public function setData(
        string $url,
        string $title,
        string $subtitle,
        string $content,
        int $image,
        int $created,
        bool $published,
        bool $static,
        bool $showdate
    ) {
        updateContentData($this->id, $url, $title, $subtitle, $content, $image, $created, $published, $static, $showdate);
    }
    /**
     * Removes the content from Database. Make sure not to use this object after doing this.
     */
    public function delete()
    {
        removeContentData($this->id);
    }

    //* read functions
    /**
     * Updates the variables in this object
     */
    public function update()
    {
        $data = getContentDataByID($this->id);
        $this->url = $data["url"];
        $this->title = $data["title"];
        $this->subtitle = $data["subtitle"];
        $this->content = $data["content_html"];
        $this->image = $data["image"];
        $this->created = $data["created"];
        $this->published = $data["published"];
        $this->static = $data["static"];
        $this->showdate = $data["showdate"];
    }
    public function getID()
    {
        return $this->id;
    }
    public function getURL()
    {
        return $this->url;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function getSubtitle()
    {
        return $this->subtitle;
    }
    public function getContent()
    {
        return $this->content;
    }
    public function getImageID()
    {
        return $this->image;
    }
    public function getCreatedTime()
    {
        return $this->created;
    }
    public function isStatic()
    {
        return $this->static;
    }
    public function isPublished()
    {
        return $this->published;
    }
    public function showDate()
    {
        return $this->showdate;
    }
}
/**
 * Get ContentItem by ID
 * @param int $id The ID of the content
 * @return ContentItem returns the ContentItem, null if not existing
 */
function getContentByID(int $id)
{
    if (contentExistsByID($id)) {
        $data = getContentDataByID($id);
        return new ContentItem(
            $id,
            $data["url"],
            $data["title"],
            $data["subtitle"],
            $data["content_html"],
            $data["image"],
            $data["created"],
            $data["published"],
            $data["static"],
            $data["showdate"]
        );
    } else {
        return null;
    }
}
/**
 * Get ContentItem by URL
 * @param string $url The URL of the content
 * @return ContentItem returns the ContentItem, null if not existing
 */
function getContentByURL(string $url)
{
    if (contentExistsByURL($url)) {
        $data = getContentDataByURL($url);
        return new ContentItem(
            $data["id"],
            $data["url"],
            $data["title"],
            $data["subtitle"],
            $data["content_html"],
            $data["image"],
            $data["created"],
            $data["published"],
            $data["static"],
            $data["showdate"]
        );
    } else {
        return null;
    }
}
/**
 * Creates a new content
 * @param string $url URL for the content
 * @param string $title the title
 * @param string $subtitle the subtitle
 * @param string $content HTML-Formatted content
 * @param int $image The mediaID for the image
 * @param int $created timestamp when content was created
 * @param bool $published public
 * @param bool $static content is static page / not
 * @param bool $showDate should the date be shown
 * @return ContentItem Returns the created ContentItem
 */
function createContent(
    string $url,
    string $title,
    string $subtitle,
    string $content,
    int $image,
    int $created,
    bool $published,
    bool $static,
    bool $showDate
) {
    if(addContentData(
        $url,
        $title,
        $subtitle,
        $content,
        $image,
        $created,
        $published,
        $static,
        $showDate
    )){
        return getContentDataByURL($url);
    }
}
