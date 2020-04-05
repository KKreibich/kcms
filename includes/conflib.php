<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/dblib.php");

class configItem{
    private $config_name;
    private $config_value;
    private DBConnector $dbconn;
    function __construct(string $config_name, string $config_value, DBConnector $dbconn)
    {
        $this->config_name = $config_name;
        $this->config_value = $config_value;
        $this->dbconn = $dbconn;
    }
    /**
     * Returns the value of the configItem
     * @return string value of the configItem
     */
    function getValue(){
        return $this->config_value;
    }
    /**
     * Returns the name of the configItem
     * @return string name of the configItem
     */
    function getName(){
        return $this->config_name;
    }
    /**
     * Reloads the data from the database
     */
    function update(){
        $data = $this->dbconn->getConfigData($this->config_name);
        $this->config_value = $data["conf_val"];
    }
    /**
     * Sets the value of the configItem & updates the data
     * @param string $value The new value for the configItem
     */
    function setValue(string $value){
        $this->dbconn->setConfigData($this->config_name, $value);
        $this->update();
    }
    /**
     * Deletes the configItem and sets values to null
     */
    function delete(){
        $this->dbconn->deleteConfigData($this->config_name);
        $this->config_name = null;
        $this->config_value = null;
    }
}

class configManager{
    private DBConnector $dbconn;
    function __construct()
    {
        $this->dbconn = new DBConnector();
    }

    /**
     * Fetches a configItem
     * @param string $name The name of the item
     * @return configItem The configItem
     */
    public function getConfigItem(string $name){
        if($this->dbconn->configExists($name)){
            $data = $this->dbconn->getConfigData($name);
            return new configItem($data["conf_name"], $data["conf_val"], $this->dbconn);
        } else {
            return null;
        }
    }

    /**
     * Creates a new configItem
     * @param string $name A unique name for the config
     * @param string $value the value for the config as string
     * @return configItem The new configItem
     */
    public function createConfigItem(string $name, string $value){
        $this->dbconn->setConfigData($name, $value);
        return $this->getConfigItem($name);
    }
}

