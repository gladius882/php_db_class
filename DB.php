<?php

class DB
{
	private $host = 'localhost';
	private $user = 'root';
	private $pass = '';
	private $dbName = 'db';
	private $tablePrefix = '';

	private $log = true;
	private $connection;
	private $valid;

	public function __construct()
	{
		$this->Connect($this->host, $this->user, $this->pass, $this->dbName);
	}

	public function __destruct()
	{
		if($this->valid) {
			$this->connection->close();
		}
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

	public function SimpleQuery($query)
	{
		$this->Log("Query executed: $query");
		return $this->connection->query($query);
	}

	public function Query($query)
	{
		// TODO bind
		$this->Log("Query executed: $query");
		return $this->connection->query($query);
	}

	public function Import($sqlFile)
	{
		$sql = file_get_contents($sqlFile);
		return $this->connection->query($sql);
	}

	public function CreateTable($table, $arr)
	{
		$query = "CREATE TABLE IF NOT EXISTS `$table`(";
		
		foreach($arr as $name => $params) {
			$query.="$name $params,";
		}
		$query = substr($query, 0, strlen($query)-1);
		$query.=") CHARACTER SET utf8 COLLATE utf8_polish_ci;";

		$this->Log("Table $table created");
		return $this->Query($query);
	}

	public function CreateRelation($table1, $column1, $table2, $column2)
	{
		return $this->Query("ALTER TABLE $table1 ADD FOREIGN KEY($column1) REFERENCES $table2($column2)");
	}

	public function DropTable($table)
	{
		$this->Log("Table $table dropped");
		return $this->Query("DROP TABLE IF EXISTS $table");
	}

	public function CreateView($view, $query)
	{
		$this->Log("View $view created");
		return $this->Query("CREATE VIEW IF NOT EXISTS $view AS $query");
	}

	public function DropView($view)
	{
		$this->Log("View $view dropped");
		return $this->Query("DROP VIEW IF EXISTS $view");
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
			$query.="'$val',";
		}
		$query = substr($query, 0, strlen($query)-1);
		$query.=");";

		$this->Log("Data inserted into $table");
		return $this->Query($query);
	}

	public function setCharset($charset)
	{
		return $this->connection->query("SET CHARSET $charset");
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

	private function Log($msg)
	{
		if($this->log == true) {
			file_put_contents("db.txt", date("Y.m.d H:i")." $this->user@$this->host : $msg".PHP_EOL, FILE_APPEND);
		}
	}
}

?>