<?php
$dbHost = "db";
$dbName = "dbname";
$dbUser = "narf";
$dbPwd = "example";

/**
 * connect to the DB and returns the connection
 */
function connectToDb(){
    global $dbHost;
    global $dbName;
    global $dbCharset;
    global $dbUser;
    global $dbPwd;
    
    $conn = NULL;
    try {
        $conn = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPwd);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);            
    }
    catch(PDOException $ex)
    {
        #if(ERR_ON_SCREEN){
            echo "Connection - failed: " . $ex->getMessage();
        #}
        #$this->log->log("Connection - failed: " . $ex->getMessage());
        return NULL;
    }
    return $conn;
}

function me(bool $direct = TRUE):string{
    if($direct){
        echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_HTML5);        
    }
    else {
        return htmlspecialchars($_SERVER['PHP_SELF'], ENT_HTML5); 
    }
    return "";
}

function insertIntoDb(string $sql, array $params = NULL, bool $isUpdate = FALSE):int {
    $ret = 0;
    try{
    $db = connectToDb();
    $stmt = $db->prepare($sql);
    $ret = $stmt->execute($params);
    if(!$isUpdate){
        $ret = $db->lastInsertId();
    }
    }catch(PDOException $pdoex ){
        $action = $isUpdate ? "Update" : "Insert";
        echo "DB $action error: ".$pdoex->getMessage()."<br>\n";
        return false;  
    }catch(Exception $ex){
        $action = $isUpdate ? "Update" : "Insert";
        echo "DB $action error: ".$ex->getMessage()."<br>\n";
        return false;
    }
    return $ret;
}

function readFromDb(string $sql, array $params = NULL):array{
    $ret = [];
    try{
    $db = connectToDb();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $ret = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    }catch(Exception $ex){
        echo "DB Read error: ".$ex->getMessage()."<br>\n";
        return [];
    }
    return $ret;
}
function getBrowserLangList():array{
    $ret = [];
    /* "de-DE,     de;     q=0.9,      en-US;      q=0.8,      en;     q=0.7"  */    
    if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
        $str = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $str = str_replace(";", ",",$str);
        $tmpArr = explode(",", $str);
        
        $lasti = -1;
        for($i = 0; $i < count($tmpArr); $i++){            
            $str = $tmpArr[$i];
            if(strpos($str, "q=") === 0){
                $tmpArr[$i] = (float)str_replace("q=", "", $str);
                array_push($ret, createLang($i,$tmpArr,$lasti));
                $lasti = $i;
            }
        } 
        uasort($ret, 'cmp');
    }
    return $ret;
}

function cmp($a, $b):int{
    return $b->compare($a->getQ());
}

function createLang(int $i, array $arr, $lasti):Lang{
    $tl = "";
    $tll = "";
    $tq = 0;
    for($j = $i; $j > $lasti; $j--){
        if($j == $i){
            $tq  = $arr[$j];
        }else if(mb_strlen($arr[$j]) > 2){
            $tll = $arr[$j];
        }else{
            $tl = $arr[$j];
        }
    }
    return new Lang($tl, $tll, $tq);
}

class Lang {

    protected $lang_lang = "";
    protected $lang = "";
    protected $q = 0;

    public function __construct(string $lang, string $lang_lang, float $q){
        $this->lang = $lang;
        $this->lang_lang = $lang_lang;
        $this->q = $q;
    }
    public function getQ(){
        return $this->q;
    }
    public function compare($q):int{
        if($this->q > $q) return 1;
        if($this->q < $q) return -1;
        return 0;
    }
}

function getFolder():string{    
    $str = realpath(dirname(__FILE__));
    $tmpArr = explode("\\",$str);
    $len = count($tmpArr);
    $tmpStr="";
    for($i = $len-2; $i < $len; $i++){
        $tmpStr.=$tmpArr[$i]."/";
    }
    return $tmpStr;    
}

