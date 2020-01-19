<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/dblib.php");

class configItem{
    private $config_name;
    private $config_value;
    function __construct(string $config_name, string $config_value)
    {
        $this->config_name = $config_name;
        $this->config_value = $config_value;
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
        $data = getConfData($this->config_name);
        $this->config_value = $data["conf_val"];
    }
    /**
     * Sets the value of the configItem & updates the data
     * @param string $value The new value for the configItem
     */
    function setValue(string $value){
        setConfigData($this->config_name, $value);
        $this->update();
    }
    /**
     * Deletes the configItem and sets values to null
     */
    function delete(){
        removeConfigData($this->config_name);
        $this->config_name = null;
        $this->config_value = null;
    }
}

function getConfigItem(string $name){
    if(confExists($name)){
        $data = getConfData($name);
        return new configItem($data["conf_name"], $data["conf_val"]);
    } else {
        return null;
    }
}

function createConfigItem(string $name, string $value){
    setConfigData($name, $value);
    return getConfigItem($name);
}