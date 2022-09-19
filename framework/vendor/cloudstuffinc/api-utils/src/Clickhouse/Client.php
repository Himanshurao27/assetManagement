<?php
namespace Cloudstuff\ApiUtil\Clickhouse;

/**
 * Clickhouse Client class for working with clickhouse cluster
 *
 * @package Cloudstuff Standard Shared Libraries
 * @author Hemant Mann <hemant.mann@cloudstuff.tech>
 * @since 1.0.2
 */
class Client {
    /**
     * Config class object
     * @var Config
     */
    protected $config;

    protected $db;
	
    public function __construct(Config $conf) {
    	$this->config = $conf;
        $this->db = new \ClickHouseDB\Client([
            'host' => $conf->host,
            'port' => $conf->port,
            'username' => $conf->username,
            'password' => $conf->password,
        ]);
    }

    public function connect(string $dbName) {
        $this->db->database($dbName);
        $pingResp = $this->db->ping();
        $this->db->settings()->max_execution_time($this->config->maxTime);
        return $pingResp;
    }

    public function execSelect($query) {
        $stmt = $this->db->select($query);
        return $stmt;
    }

    public function selectAll($query) {
        $stmt = $this->execSelect($query);
        return $stmt->rows();
    }

    /**
     * Use the wrapper provided by the library to insert UInt64 type values
     * @param  string|int $val Int64 value
     */
    public function convertToUInt64($val) {
        return \ClickHouseDB\Type\UInt64::fromString($val);
    }

    /**
     * This method inserts multiple rows at once in the database
     * @param  string $table   Name of the table
     * @param  array $rows    [[v11, v12], [v21, v22]]
     * @param  array $columns Name of the columns
     * @return object Library statment object
     * @throws \ClickHouseDB\Exception\ClickHouseException Could be any of the clickhouse exception objects
     */
    public function insert($table, $rows, $columns) {
        return $this->db->insert($table, $rows, $columns);
    }

    /**
     * Use this function to execute RAW queries on the clickhouse database
     * @param  string $stmt SQL statement
     * @return mixed
     */
    public function write($stmt) {
        return $this->db->write($stmt);
    }

    /**
     * This method exports the data to a file on system, the file should have write access
     * 
     * @param  array $query    The query array
     * @param  string $filePath Path to file
     * @param string $format The format in which data is to be exported
     */
    public function exportToFile($query, $filePath, $format = 'csv') {
        $formatConst = $format == 'csv' ? \ClickHouseDB\Query\WriteToFile::FORMAT_CSV : 'TabSeparated';
        $writeToFile = new \ClickHouseDB\Query\WriteToFile($filePath, true, $formatConst);
        $this->db->select($query, [], null, $writeToFile);
    }
}
