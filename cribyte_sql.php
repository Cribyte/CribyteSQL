<?php
//-------------------------------------------
//-------------------------------------------
//(C) Cribyte Development - Model Class 2018
//[Version: 1.0] 
//[Author: Kristian Batalo]
//[Information: dev.cribyte.com]
//-------------------------------------------

//Extensions 
//-------------------------------------------
//require_once "YOUR_EXTENSION.php"; //Extension 1
//-------------------------------------------

class CribyteSQL{

    protected $conn = null;  //DB connection resources

    //---------------------------Editable-------------------------------------------------
        //TRUE track coming SQL Statements in log.txt / FALSE by Default - don't track SQL Statements
        private $log = true;

        //Database Standard Configuration
        private $host = "YOUR DATABASE HOST";         //Database Host/Server
        private $user = "YOUR DATABASE USERNAME";     //Database Username
        private $password = "YOUR DATABASE PASSWORD"; //Database Password
        private $dbname = "YOUR DATABASE NAME";       //Database Name
        private $port = "3306";                       //Database Server Port [Default: 3306]
    //------------------------------------------------------------------------------------
  
    //MySQL Construct START

        public function __construct($config = array()){

          //Database Data Initialize START
          $host = isset($config['host']) ? $config['host'] : $this->host;
          $user = isset($config['user']) ? $config['user'] : $this->user;
          $password = isset($config['password']) ? $config['password'] : $this->password;
          $dbname = isset($config['dbname']) ? $config['dbname'] : $this->dbname;
          $port = isset($config['port']) ? $config['port'] : $this->port;
          //Database Data Initialize END

          //Database Connection START  
            try
            {
                $this->conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $password);
                // set the PDO error mode to exception
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, TRUE);
                echo "[$dbname] Connected successfully";
            }
            catch(PDOException $e) 
            {
                echo "[$dbname] Connection failed: " . $e->getMessage();
            }
          //Database Connection END

        }

    //MySQL Construct END

        
       
    private function cbTrack($sql){
        
        if ($this->log==TRUE) {
            // Write SQL statement into log
            $str = $sql . "  [". date("Y-m-d H:i:s") ."]" . PHP_EOL;
            file_put_contents("log.txt", $str,FILE_APPEND);
        }
        else {
            return false;
        }
    }

    public function querySQL($sql){
        
        $this->cbTrack($sql);        
        
        try {
            
            $conn->exec($sql);

            return true;
        }
        catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

    }

    public function selectSQL($table,$limit = null, $sql=null){
        
        $sql = ((!$sql) ? "Select * from $table" : $sql).(($limit) ? " lIMIT $limit" : "");
        $this->cbTrack($sql);
        $result = null;
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            //Test Debugging Variable $result
            self::cbDebug($result);

            return $result;
        }
        catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

    }

    public function insertSQL($table,$values=array(),$col=array()){

        $sql = "INSERT INTO $table ".$this->specificColMySQL($col)." VALUES ".$this->insertValuesMySQL($values);
        $this->cbTrack($sql);
        
        try {
            // prepare sql and bind parameters
            $stmt = $this->conn->prepare($sql);
            
            for ($i=0; $i < (count($values)) ; $i++) { 
                $stmt->bindParam(':insert'.$i, $values[$i]);
            }

            $stmt->execute();
            return true;
        }
        catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

    }

    public function updateSQL($table,$values=array(),$col=array(),$where){

        $sql = "Update $table SET ".$this->updateStringMySQL($values,$col)." WHERE ".$where;
        $this->cbTrack($sql);
        
        try {
            // prepare sql and bind parameters
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return true;
        }
        catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

    }

    public function deleteSQL($table,$where=null){

        $sql = "Delete from $table".(($where) ? " WHERE ".$where : "");
        $this->cbTrack($sql);
        
        try {
            // prepare sql and bind parameters
            $stmt = $this->conn->prepare($sql);

            $stmt->execute();
           
            return true;
        }
        catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

    }

    public function lastIdSQL(){

        return $this->conn->lastInsertId();

    }

    private static function cbDebug($result)
    {
        foreach ($result as $row) 
        {
            echo '<pre>';
            print_r($row);
            echo '</pre>';
        }
    }

    private function insertValuesMySQL($values)
    {
        $valuestring='(';
        for ($i=0; $i < (count($values)) ; $i++) { 
            $valuestring.= (($i < ((count($values))-1)) ? ':insert'.$i.',' : ':insert'.$i);
        }
        return $valuestring.')';
    }

    private function specificColMySQL($col=null)
    {
        if(!$col)
        {

            return null;
        
        } 
        else 
        {

            $valuestring="(";
            
            for ($i=0; $i < (count($col)) ; $i++) { 
                $valuestring.= (($i < ((count($col)))-1) ? $col[$i].',' : $col[$i]);
            }

            return $valuestring.")";

        }
    }

    private function updateStringMySQL($values,$col)
    {
        $string = null;
        for ($i=0; $i < count($values); $i++) { 
            $string.= (($i < ((count($values))-1)) ? $col[$i].'='.$values[$i].',' : $col[$i].'='.$values[$i]);
        }

        return $string;
    }

}
