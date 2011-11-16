<?php
/*
 * HOMER Web Interface
 * Homer's pdo.php
 *
 * Copyright (C) 2011-2012 Alexandr Dubovikov <alexandr.dubovikov@gmail.com>
 * Copyright (C) 2011-2012 Lorenzo Mangani <lorenzo.mangani@gmail.com>
 *
 * The Initial Developers of the Original Code are
 *
 * Alexandr Dubovikov <alexandr.dubovikov@gmail.com>
 * Lorenzo Mangani <lorenzo.mangani@gmail.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
*/

defined( '_HOMEREXEC' ) or die( 'Restricted access' );


class HomerDB {
	//database setup 

       //MAKE SURE TO FILL IN DATABASE INFO
	var $hostname_logon = HOST;		//Database server LOCATION
	var $port_logon = PORT;			//Database PORT default MYSQL
	var $database_logon = DB;		//Database NAME
	var $username_logon = USER;		//Database USERNAME
	var $password_logon = PW;		//Database PASSWORD

	var $hostname_homer = HOMER_HOST;	//Database server LOCATION
	var $port_homer = HOMER_PORT ;		//Database server PORT. default MYSQL
	var $database_homer = HOMER_DB;		//Database NAME
	var $username_homer = HOMER_USER;	//Database USERNAME
	var $password_homer = HOMER_PW;		//Database PASSWORD
	
	//table fields
	var $user_table = 'homer_logon';	//Users table name
	var $user_column = 'useremail';		//USERNAME column (value MUST be valid email)
	var $pass_column = 'password';		//PASSWORD column
	var $user_level = 'userlevel';		//(optional) userlevel column
	
	//encryption
	var $encrypt = true;		//set to true to use md5 encryption for the password
	/* CONNECT */
	protected $connection;		//Our connection

	//connect to database
	function dbconnect(){

		if(!$this->port_logon) $this->port_logon=3306; // Set to default		      
		
		try {
		      $dbstring = DATABASE.":host=".$this->hostname_logon.";port=".$this->port_logon.";dbname=".$this->database_logon;
		      $this->connection = new PDO($dbstring, $this->username_logon, $this->password_logon);
		} catch (PDOException $e){
		      die($e->getMessage());
		}
		return;
	}

	//connect to database
	function dbconnect_homer($host){
	        
                if(!$host) $host = $this->hostname_homer;	                        
                if(!$this->port_logon) $this->port_logon=3306; // Set to default		      
	
                try {
		      $dbstring = DATABASE.":host=".$host.";port=".$this->port_homer.";dbname=".$this->database_homer;
		      $this->connection = new PDO($dbstring, $this->username_homer, $this->password_homer);
		} catch (PDOException $e){
		      die($e->getMessage());
		}                
                
		return true;
	}
		
	//prevent injection
	function qry($query) {
	      $this->dbconnect();
              $args  = func_get_args();
              $query = array_shift($args);
              $query = str_replace("?", "%s", $query);
              $args  = array_map($this->connection->quote, $args);
              array_unshift($args,$query);
              $query = call_user_func_array('sprintf',$args);              
 	      $statement = $this->connection->prepare($query);
 	      $statement->execute(); 	                      
              $result = $statement->fetch();              
              if($result){
                      return $result;
	      }else{
	              $error = "Error";
	              return $result;
              }
        }
        
        //prevent injection
	function makeQuery($query) {
	      $this->dbconnect();
              $args  = func_get_args();
              $query = array_shift($args);
              $query = str_replace("?", "%s", $query);
              $args  = array_map($this->connection->quote, $args);
              array_unshift($args,$query);
              $query = call_user_func_array('sprintf',$args);
              return $query;
        }
	
	function getAliases($table='hosts', $key=''){
		//conect to DB
		$this->dbconnect();
		
		$query = "SELECT id,host,name FROM homer_".$table." WHERE status = 1";
		return $this->loadObjectList($query);		

	}

	function executeQuery($query) {			
		//$result = mysql_query($query);
		
		$statement = $this->connection->prepare($query);
		$statement->execute();

		if(!$result) return false;
		else return true;
	}

	function loadObjectList($query) {
	
		$statement = $this->connection->prepare($query);
		$statement->execute();		                	  
	        $result = $statement->fetchAll(PDO::FETCH_CLASS);
	        return $result;	        
	}
	
	function loadObjectArray($query) {
	
		$statement = $this->connection->prepare($query);
		$statement->execute();		                	  
	        $result = $statement->fetchAll();
	        return $result;
	}

	function loadResult($query)
	{
        	$statement = $this->connection->prepare($query);
        	$statement->execute();        	                        	
        	$result = $statement->fetch(PDO::FETCH_NUM);
        	return $result[0];        	                                             	
	}		
}

?>