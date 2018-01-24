<?php

class DB
{
	private $host = 'localhost';
	private $user = '';
	private $pass = '';
	private $dbName = '';

	private $log = true;
	private $connection;
	private $valid;

	private function Log($msg)
	{
		if($this->log == true) {
			file_put_contents("db.txt", date("Y.m.d H:i")." $msg".PHP_EOL, FILE_APPEND);
		}
	}

	public function __construct()
	{
		$this->Connect($this->host, $this->user, $this->pass, $this->dbName);
	}

	public function Connect($host, $user, $pass, $dbName)
	{
		$this->connection = new mysqli($host, $user, $pass);
		$this->CheckConnection();

		if($this->valid == true)
		{
			$this->connection->select_db($dbName);
			$this->setCharset('utf8');
		}
	}

	public function Query($query)
	{
		// TODO bind and escape
		$result = $this->connection->query($query);
		$this->Log("Query executed on $this->user@$this->host: $query");
		return $result;
	}

	public function CreateTable($table, $arr)
	{
		$query = "CREATE TABLE IF NOT EXISTS `$table`(";
		
		foreach($arr as $name => $params) {
			$query.="`$name` $params,";
		}
		$query = substr($query, 0, strlen($query)-1);
		$query.=");";

		return $this->Query($query);
	}

	public function DropTable($table)
	{
		return $this->Query("DROP TABLE $table");
	}

	public function Insert($table, $arr)
	{
		$query = "INSERT INTO $table(";
		foreach($arr as $col => $val) {
			$query.="$col,";
		}
		$query = substr($query, 0, strlen($query)-1);
		$query.=") VALUES(";

		foreach($arr as $col => $val) {
			$query.="$val,";
		}
		$query = substr($query, 0, strlen($query)-1);
		$query.=");";

		return $this->Query($query);
	}

	public function setCharset($charset)
	{
		$this->connection->query("SET CHARSET $charset");
	}

	public function CheckConnection()
	{
		if(mysqli_connect_errno()) {
			$this->valid = false;
		} else {
			$this->valid = true;
		}

		return $this->valid;
	}

	public function Disconnect()
	{
		if($this->valid == true) {
			$this->connection->close();
		}
	}
}

?>