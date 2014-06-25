<?php

class Zhort {

// Database Configuration
    private $_dbName = '';
    private $_dbUser = '';
    private $_dbPass = '';
    private $_dbHost = '';
    private $_dbTableNames = 'zhort_names';
    private $_dbTable = 'zhort';

    private $_url = '';
    private $_name = '';
    private $_db;

    public function __construct() {
        $this->_db = new PDO('mysql:host=' . $this->$_dbHost.';dbname='. $this->$_dbName, $this->$_dbUser, $this->$_dbPass);
    }

    /**
     * @param $url
     */
    public function setUrl($url) {
        $this->_url = $url;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return $this->_url;
    }

    /**
     * @param $name
     */
    public function setName($name) {
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->_name;
    }




    /**
     * @return bool|string
     * @throws Exception
     * add _url to database, if _name isn't empty also adds it to db
     * returns id or name of url (if given)
     */
    public function addUrl() {

        $this->_fixUrl();
        $found = $this->_findDoubles();
        if($found == false) {

            if($this->_name != '') {

                if($this->_checkNameAvailability($this->_name)) {
                    $ids = $this->_addUrlToDB();
                    $this->_addNameToDB($ids);
                    return $this->_name;
                }
                else {
                    throw new Exception('name\'s already taken');
                }
            }
            else {
                $ids = $this->_addUrlToDB();
                return $ids;
            }
        }

        else {
            if($this->_name != '') {
                if(!$this->_getNameByIdFromDB($found) || $this->_getNameByIdFromDB($found)!=$this->_name) {
                    if($this->_checkNameAvailability($this->_name)) {
                        $this->_addNameToDB($found);
                        return $this->_name;
                    }
                    else {
                        throw new Exception('name\'s already taken');
                    }

                }
                else {
                    return $this->_name;
                }
            }

            else {
                return $found;
            }


        }

    }


    /**
     * @param $val
     * @return mixed
     */
    public function getUrlFromDB($value) {


        $st = "SELECT url FROM " . $this->_dbTableNames . " n
                LEFT JOIN " . $this->_dbTable . " u on (n.url_id=u.id and name=:name)
            ";

        $ex = $this->_db->prepare($st);
        $ex->bindValue(':name', $value);
        $ex->execute();

        if($ex->rowCount() == 0) {

            $st = "SELECT url FROM " . $this->_dbTable . " WHERE id=:id";
            $ex = $this->_db->prepare($st);
            $ex->bindValue(':id', $value);
            $ex->execute();

        }

        return $ex->fetch(PDO::FETCH_COLUMN);


    }

    /**
     * @param $id
     * @return mixed
     */
    private function _getNameByIdFromDB($id) {

        $st = "SELECT name FROM " . $this->_dbTableNames . " WHERE url_id=:id";
        $ex = $this->_db->prepare($st);
        $ex->bindValue(':id', $id);
        $ex->execute();
        return $ex->fetch(PDO::FETCH_COLUMN);

    }


    /**
     * @param $name
     * @return bool
     * return true if name is avaliable
     */
    private function _checkNameAvailability($name) {

        $st = "SELECT id FROM " . $this->_dbTableNames . " WHERE name=:name";
        $ex = $this->_db->prepare($st);
        $ex->bindValue(':name', $name);
        $ex->execute();



        return $ex->rowCount()==0;


    }


    /**
     * @return string
     * @throws Exception
     */
    private function _addUrlToDB() {

        if($this->_url == '') throw new Exception('no url detected');

        $prepared = $this->_db->prepare("INSERT INTO " . $this->_dbTable . '(url) VALUES(:url)');
        $prepared->execute(array(':url' => $this->_url));
        return $this->_db->lastInsertId();

    }


    /**
     * @param $id
     * @throws Exception
     */
    private function _addNameToDB($id) {

        if($this->_name == '') throw new Exception('no name given');

        $prepared2 = $this->_db->prepare("INSERT INTO " . $this->_dbTableNames . '(url_id, name) VALUES(:url_id, :name)');
        $prepared2->execute(array(':url_id' => $id, ':name'=> $this->_name));

    }



    /**
     * @return bool
     * look if there's already url in the database
     */
    private function _findDoubles() {

        $st = "SELECT id FROM " . $this->_dbTable . " WHERE url=:url";
        $ex = $this->_db->prepare($st);
        $ex->bindValue(':url', $this->_url);
        $ex->execute();
        $result = $ex->fetch(PDO::FETCH_COLUMN);
        if(!empty($result)) {
            return $result;
        }
        else {
            return false;
        }

    }




    /**
     * @return bool
     * @throws Exception
     * validate url
     */
    public function validUrl() {

        $pattern = '/^(http(s)?:\/\/)?(w{3}\.)?[a-z\.\-]+\.[a-z]{2,3}(\/.*)?$/i';
        if(!preg_match($pattern, $this->_url)) {
            throw new Exception('invalid url');
        }
        return true;

    }




    /**
     * add 'http://' at the begin and '/' at the end of url
     */
    private function _fixUrl() {


        if(!preg_match('/^https?:\/\//i', $this->_url)) {
            $this->_url = 'http://' . $this->_url;
        }

        if(!preg_match('/\/$/', $this->_url)) {
            $this->_url .= '/';
        }

    }

}


