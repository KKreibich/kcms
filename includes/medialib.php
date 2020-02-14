<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/includes/dblib.php");

class mediaItem
{
    private $id;
    private $name;
    private $type;
    private $path;
    private $desc;
    function __construct(int $id, string $name, int $type, string $path, string $desc)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->path = $path;
        $this->desc = $desc;
    }
    /**
     * @return int The ID of the Item
     */
    public function getID()
    {
        return $this->id;
    }
    /**
     * @return string The Description of the item
     */ 
    public function getName()
    {
        return $this->name;
    }
    /**
     * @return int The ID of the type of the item
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * @return string The Path to the storage location
     */
    public function getPath()
    {
        return $this->path;
    }
    /**
     * @return string The Description of the item
     */
    public function getDescription()
    {
        return $this->desc;
    }
    /**
     * Set new Data for the Item in the Database
     * @param string $name The new name
     * @param int $type The new type
     * @param string $path The new path 
     * @param string $desc The new description
     */
    public function setData(string $name, int $type, string $path, string $desc){
        updateMediaData($this->id, $name, $type, $path, $desc);
        $this->updateVars();
    }
    public function updateVars(){
        $data = getMediaData($this->id);
        $this->name = $data["name"];
        $this->type = $data["type"];
        $this->path = $data["path"];
        $this->desc = $data["desc"];
    }
}

/**
 * Gets a mediItem from the database
 * @param int $id ID of the object
 * @return mediaItem The item
 */
function getMedia(int $id){
    $data = getMediaData($id);
    if($data != null){
        return new mediaItem($data["id"], $data["name"], $data["type"], $data["path"], $data["desc"]);
    } else {
        return null;
    }
}

/**
 * Gets all media-object from the database
 * @return array Array with all mediaItems
 */
function getAllMedia(){
    $dataArray = getAllMediaData();
    $objArray = array();
    foreach($dataArray as $data){
        array_push($objArray, new mediaItem($data["id"], $data["name"], $data["type"], $data["path"], $data["path"]));
    }
    return $objArray;
}