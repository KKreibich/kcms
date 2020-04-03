<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/includes/dblib.php");

class HttpRoute{
    private int $id;
    private string $name;
    private string $url;
    private int $type;
    private ?int $z_code;
    private ?string $z_url;
    private ?int $o_pgid;

    function __construct(int $id,
    string $name,
    string $url,
    int $type,
    ?int $z_code,
    ?string $z_url,
    ?int $o_pgid){

        $this->id = $id;
        $this->name = $name;
        $this->url = $url;
        $this->type = $type;

        if($this->type == 0&&$z_code != null&&$z_url != null){
            $this->z_code = $z_code;
            $this->z_url = $z_url;
            $this->o_pgid = null;
        }elseif($this->type == 1&&$o_pgid != null){
            $this->z_code = null;
            $this->z_url = null;
            $this->o_pgid = $o_pgid;
        } else {
            throw new routeTypeException();
        }
    }
    /**
     * Updates the variables in this class
     */
    private function updateData(){
        $data = getRouteData($this->id);
        $this->name = $data["name"];
        $this->url = $data["url"];
        $this->type = $data["type"];
        if($this->type == 0&&$data["0_code"] != null&&$data["0_url"] != null){
            $this->z_code = $data["0_code"];
            $this->z_url = $data["0_url"];
            $this->o_pgid = null;
        } elseif($this->type == 1&&$data["1_pgid"] != null){
            $this->z_code = null;
            $this->z_url = null;
            $this->o_pgid = $data["1_pgid"];
        } else {
            throw new routeTypeException();
        }
    }

    /**
     * Updates the Data for an HttpRoute in the Database
     * @param string $name The new name
     * @param string $url The new URL
     * @param int $type The HttpRoute-Type (0 or 1)
     * @param int $z_code The Http-Redirect Code (Use only if type=0)
     * @param string $z_url The URL to redirect to (Use only if type=0)
     * @param int $o_pgid The ID of the page to schow (Use only if type=1)
     */
    public function setData(string $name, string $url, int $type, ?int $z_code, ?string $z_url, ?int $o_pgid){
        updateRouteData($this->id, $name, $url, $type, $z_code, $z_url, $o_pgid);
        $this->updateData();
    }

    /**
     * @return string The name of the route
     */
    public function getName(){
        $this->updateData();
        return $this->name;
    }

    /**
     * @return string The URL of the route
     */
    public function getURL(){
        $this->updateData();
        return $this->url;
    }

    /**
     * @return int The HttpRoute-type
     */
    public function getType(){
        $this->updateData();
        return $this->type;
    }

    /**
     * Only use if type=0
     * @return int The Http-Redirect-Code
     */
    public function getRedirectCode(){
        $this->updateData();
        if($this->type == 0){
        return $this->z_code;
        } else {
            throw new routeTypeException();
        }
    }

    /**
     * Only use if type=0
     * @return string The redirect-url
     */
    public function getRedirectURL(){
        $this->updateData();
        if($this->type == 0){
        return $this->z_url;
        } else {
            throw new routeTypeException();
        }
    }
    /**
     * Only use if type=1
     * @return int The page-id
     */
    public function getPageID(){
        $this->updateData();
        if($this->type == 1){
        return $this->o_pgid;
        } else {
            throw new routeTypeException();
        }
    }
    
    /**
     * Deletes the route.
     */
    public function delete(){
        deleteRouteData($this->id);
    }
}

class HttpRouteManager{
    //* Paused until objective DB-Management
}

/**
 * Thrown when given or requested variables are not matching the HttpRoute-Type.
 */
class routeTypeException extends Exception{
    public function errorMessage() {
        //error message
        $errorMsg = 'Error on line '.$this->getLine().' in '.$this->getFile()
        .': Given or requested variables are not matching HttpRoute-Type.';
        return $errorMsg;
      }
}