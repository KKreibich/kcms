<?php 
include($_SERVER["DOCUMENT_ROOT"]."/config.php");

class user{
    private $id;
    private $role;
    private $name;
    private $pass_hash;
    private $author_name;
    private $email;
    /**
     * Creates the user object
     * @param int $id ID
     * @param int $role the ID of the role
     * @param string $name username
     * @param string $pass_hash the hashed password
     * @param string $author_name author name of the user
     * @param string $email email
     */
    function __construct(int $id, int $role, string $name,
    string $pass_hash, string $author_name, string $email)
    {
        $this->id = $id;
        $this->role = $role;
        $this->name = $name;
        $this->pass_hash = $pass_hash;
        $this->author_name = $author_name;
        $this->email = $email;
    }
    /**
     * Updates the data for the user
     */
    public function update(){
        $data = getUserData($this->id);
        $this->role = $data["role"];
        $this->name = $data["name"];
        $this->pass_hash = $data["pass_hash"];
        $this->author_name = $data["author_name"];
        $this->email = $data["email"];
    }
    /**
     * Get the name of the user
     * @return string name of the user
     */
    public function getName(){
        return $this->name;
    }
    /**
     * Get the ID of the user
     * @return int ID of the user
     */
    public function getID(){
        return $this->id;
    }
    /**
     * Return the RoleID of the user
     * @return int ID of the role of the user
     */
    public function getRole(){
        return $this->role;
    }
    /**
     * Validates the password
     * @param string $pass the user intput
     * @return bool true if password is correct, false if not
     */
    public function valPass(string $pass_input){
        return password_verify($pass_input, $this->pass_hash);
    }
    /**
     * Get the author-name of the user
     * @return string the auhtor-name
     */
    public function getAuthorName(){
        return $this->author_name;
    }
    /**
     * Get the e-mail adress of the user
     * @return string e-mail adress
     */
    public function getEmail(){
        return $this->email;
    }
    /**
     * Sets the data to the database
     * @param int $role RoleID
     * @param string $name username
     * @param string $author_name author-name
     * @param string $email e-mail adress
     */
    public function setData(int $role, string $name, string $author_name, string $email){
        updateUserData($this->id, $role, $name, $this->pass_hash, $author_name, $email);
    }
    /**
     * Sets a new password for the user
     * @param string $password clear password (not hashed)
     * @return bool true if data was successfull written
     */
    public function setPassword(string $password){
        $hash = password_hash($password, PASSWORD_BCRYPT);
        updateUserData($this->id, $this->role, $this->name, $hash, $this->author_name, $this->email);
    }
}
