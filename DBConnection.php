<?php

/******************************************************************************************
	
	DB Connection class
	Based on GPL code written by Thimal Jayasooriya 
	for Colombo bus route project
	
******************************************************************************************/

class DBConnection
{
	private $db;
	
	/* db connection properties = EDIT HERE */
	private $dbhost = "localhost";
	private $dbname = "kottu";
	private $dbuser = "root";
	private $dbpwd = '';
	
	public function __construct($conn = true) 
	{
		if ($conn)
		{
			$this->connect();
		}
	}


	function connect() 
	{
		$dsn = "mysql:host=" . $this->dbhost . ";dbname=" . $this->dbname;

	    	try 
		{
			$this->db = new PDO($dsn, $this->dbuser, $this->dbpwd);
			$this->db->exec("set names utf8");
		} 
		catch (PDOException $e)
		{
			print "Database connection failed: " . $e->getMessage() . "<br/>";
			die();    
		}
	}

	function query($sql, $params)
	{		
		
		$statement = $this->db->prepare($sql);
	    	$result = $statement->execute($params);
		
		return $result? $statement:$result;
	}
}

?>
