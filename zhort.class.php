<?php

class Zhort {

// Database Configuration
    static public $dbName = '';
    static public $dbUser = '';
    static public $dbPass = '';
    static public $dbHost = '';
    static public $dbTableNames = 'zhort_names';
    static public $dbTable = 'zhort';

    private $_url = '';
    private $_name = '';
    private $_db;

    public function __construct() {
        $this->_db = new PDO('mysql:host='.self::$dbHost.';dbname='. self::$dbName, self::$dbUser, self::$dbPass);
    }

    public function setUrl($url) {
        $this->_url = $url;
    }

    public function getUrl() {
        return $this->_url;
    }

    public function setName($name) {
        $this->_name = $name;
    }

    public function getName() {
        return $this->_name;
    }



//adding $this->_url to database, if there variable _name isn't empty also adds it to db
//before checks if there's url in table or name's taken.
//returns id or name of url (if given)
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






    public function getUrlFromDB($val) {


//        $r = "SELECT ". self::$dbTableNames . ".name as tname, " . self::$dbTableNames . ".url_id as turl_id, " . self::$dbTable . ".url as turl, " .self::$dbTable . ".id as tid FROM " . self::$dbTableNames . "," . self::$dbTable . " WHERE tname=" . $val . " AND tid=";
        $r = "SELECT url_id FROM " . self::$dbTableNames . " WHERE name='" . $val ."'";
        $result6 = $this->_db->query($r);
        $resArr = $result6->fetch();
        if($result6->rowCount() > 0) {

            $t = "SELECT url FROM " . self::$dbTable . " WHERE id='" . $resArr[0] . "'";
            $result4 = $this->_db->query($t);
            $resArr = $result4->fetch();
            return $resArr[0];

        }
        else {
            $y = "SELECT url FROM " . self::$dbTable . " WHERE id='" . $val . "'";
            $result4 = $this->_db->query($y);
            $resArr = $result4->fetch();
            return $resArr[0];
        }


    }
    
    private function _getNameByIdFromDB($id) {
        
        
        $e = "SELECT name FROM " . self::$dbTableNames . " WHERE url_id=" . $id;
        $result = $this->_db->query($e);

            $resArr = $result->fetch();
            return $resArr[0];
    }

//gets true if name's avaliable
    private function _checkNameAvailability($name) {

        $t = "SELECT id FROM " . self::$dbTableNames . " WHERE name='" . $name ."'";
        $result3 = $this->_db->query($t);


        if($result3->rowCount()==0) {
            return true; }
         else {
            return false;
        }

    }
    
    
    

    private function _addUrlToDB() {
        
        if($this->_url == '') throw new Exception('no url detected');

        $prepared = $this->_db->prepare("INSERT INTO " . self::$dbTable . '(url) VALUES(:url)');
        $prepared->execute(array(':url' => $this->_url));
        return $this->_db->lastInsertId();

    }


    private function _addNameToDB($id) {

        if($this->_name == '') throw new Exception('no name given');

        $prepared2 = $this->_db->prepare("INSERT INTO " . self::$dbTableNames . '(url_id, name) VALUES(:url_id, :name)');
        $prepared2->execute(array(':url_id' => $id, ':name'=> $this->_name));

    }


    //looks if there's already url in database
    private function _findDoubles() {

        $q = "SELECT id FROM " . self::$dbTable . " WHERE url='" . $this->_url . "'";
        $str = $this->_db->query($q);
        if($str->rowCount()>0) {
            $resArr = $str->fetch();
            return $resArr[0];
        }

        else {
            return false;
        }

    }



    //valids if url's correct
    public function validUrl() {

        $pattern = '/^(http(s)?:\/\/)?(w{3}\.)?[a-z\.\-]+\.[a-z]{2,3}(\/.*)?$/i';
        if(!preg_match($pattern, $this->_url)) {
            throw new Exception('invalid url');
        }
        else return true;

    }



    //adds 'http://' at the begin and '/' at the end if isn't find
    private function _fixUrl() {


        if(!preg_match('/^https?:\/\//i', $this->_url)) {
            $this->_url = 'http://' . $this->_url;
        }

        if(!preg_match('/\/$/', $this->_url)) {
            $this->_url .= '/';
        }

    }

}


