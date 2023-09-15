<?php 
/*
@Author Cristiano MCon - cristiano.mcon@gmail.com		
*/

class Database {
    private $pdo,$sql,$bd='mysql',$user='root',$pass='',$host='localhost';
    public $erro=false;

    public function conecta(){    
        try {
            $this->pdo = new PDO('mysql:host='.$this->host.';dbname='.$this->bd,$this->user,$this->pass);
        } catch(PDOException $e){                    
             $this->erro = $e->getMessage();            
        }   
        return $this->erro ? false : true;
    }

    public function setSQL($sql){
        $this->sql = $sql;
    }
    
    public function executa(){
        try { 
            return $this->pdo->query($this->sql);                   
        } catch (PDOException $e) {                     
            echo $e->getMessage();
        }
    }
}