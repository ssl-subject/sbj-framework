<?php

class CMysql {
	private $_connection;
	private $_statment;
	private $_prefix;
	private $_suffix;

	public function __construct($host=null, $dbname=null, $user=null, $pass=null) {
		if($host == null) $host = SBJ::config("db", "host");
		if($dbname == null) $dbname = SBJ::config("db", "base");
		if($user == null) $user = SBJ::config("db", "user");
		if($pass == null) $pass = SBJ::config("db", "pass");

		$this->_connection = new PDO('mysql:host='. $host .';dbname='.$dbname, $user, $pass);
		$this->_prefix = SBJ::config("db", "prefix");
		$this->_suffix = SBJ::config("db", "suffix");
		$this->query("SET NAMES utf8");
		/*
		[PDO::ATTR_PERSISTENT => true]
		*/
	}

	public function getTable() {
		throw new Exception("Table not specified.", 500);
	}

	public function query($sql, $params=[]) {
		$this->_statment = $this->_connection->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$common = [];


		$this->_statment->execute(array_merge($params, $common));
		$err = $this->errorInfo();

		file_put_contents(RUNTIME . "/" . date("d.m.Y") . ".log", "SQL: ". $sql ." | ". json_encode($params) ."\n" . " - " . json_encode($err), FILE_APPEND);
		return $this;
	}

	public function __call($method, $args) {
		if(method_exists($this->_statment, $method)) return call_user_func_array([$this->_statment, $method], $args);
		else if(method_exists($this->_connection, $method)) return call_user_func_array([$this->_connection, $method], $args);
	}
}

?>
