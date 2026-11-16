<?php
/**
 * Database Class - COMPLETE VERSION with All Features
 * Teddy Bear Shop - Enhanced Database Handler
 */

class Database {
    private $conn;
    private $transaction_started = false;
    private $query_count = 0;
    private $last_query = '';
    private $stmt_params = []; // Store params for statements

    
    public function __construct() {
        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset('utf8mb4');
            
            // Set SQL mode for better compatibility
            $this->conn->query("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
            
            if (APP_DEBUG) {
                error_log("Database connected successfully to " . DB_NAME);
            }
            
        } catch (Exception $e) {
            if (APP_DEBUG) {
                die("Database Error: " . $e->getMessage());
            } else {
                die("Đang có lỗi kỹ thuật. Vui lòng thử lại sau!");
            }
        }
    }
    
    /**
     * Get the MySQLi connection object
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Fetch single row from database
     */
    public function fetch($sql, $params = []) {
        try {
            $this->last_query = $sql;
            $this->query_count++;
            
            $stmt = $this->prepare($sql, $params);
            if (!$stmt) {
                return null;
            }
            
            // Bind parameters if any
            if (!empty($stmt->query_params)) {
                if (!$this->bindParams($stmt, $stmt->query_params)) {
                    return null;
                }
            }
            
            if (!$stmt->execute()) {
                error_log("Execute error: " . $stmt->error . " | Query: " . $sql);
                return null;
            }
            
            $result = $stmt->get_result();
            if (!$result) {
                error_log("Get result error: " . $this->conn->error);
                return null;
            }
            
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return $row;
            
        } catch (Exception $e) {
            error_log("Database fetch error: " . $e->getMessage() . " | Query: " . $sql);
            return null;
        }
    }
    
    /**
     * Fetch multiple rows from database
     */
    public function fetchAll($sql, $params = []) {
        try {
            $this->last_query = $sql;
            $this->query_count++;
            
            $stmt = $this->prepare($sql, $params);
            if (!$stmt) {
                return [];
            }
            
            // Bind parameters if any
            if (!empty($stmt->query_params)) {
                if (!$this->bindParams($stmt, $stmt->query_params)) {
                    return [];
                }
            }
            
            if (!$stmt->execute()) {
                error_log("Execute error: " . $stmt->error . " | Query: " . $sql);
                return [];
            }
            
            $result = $stmt->get_result();
            if (!$result) {
                error_log("Get result error: " . $this->conn->error);
                return [];
            }
            
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $rows;
            
        } catch (Exception $e) {
            error_log("Database fetchAll error: " . $e->getMessage() . " | Query: " . $sql);
            return [];
        }
    }
    
    /**
     * Execute query (UPDATE, DELETE, etc.)
     */
    public function execute($sql, $params = []) {
        try {
            $this->last_query = $sql;
            $this->query_count++;
            
            $stmt = $this->prepare($sql, $params);
            if (!$stmt) {
                return false;
            }
            
            // Bind parameters if any
            if (!empty($stmt->query_params)) {
                if (!$this->bindParams($stmt, $stmt->query_params)) {
                    return false;
                }
            }
            
            $result = $stmt->execute();
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            
            if (APP_DEBUG && $affected_rows > 0) {
                error_log("Query executed successfully. Affected rows: " . $affected_rows);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Database execute error: " . $e->getMessage() . " | Query: " . $sql);
            return false;
        }
    }
    
    /**
     * Insert data and return inserted ID
     */
    public function insert($sql, $params = []) {
        try {
            $this->last_query = $sql;
            $this->query_count++;
            
            $stmt = $this->prepare($sql, $params);
            if (!$stmt) {
                error_log("Insert prepare failed: " . $this->conn->error);
                return false;
            }
            
            // Bind parameters if any
            if (!empty($stmt->query_params)) {
                if (!$this->bindParams($stmt, $stmt->query_params)) {
                    error_log("Insert bind_param failed");
                    return false;
                }
            }
            
            if ($stmt->execute()) {
                $insert_id = $this->conn->insert_id;
                $stmt->close();
                
                if (APP_DEBUG) {
                    error_log("Insert successful. ID: " . $insert_id);
                }
                
                return $insert_id;
            } else {
                error_log("Insert execute failed: " . $stmt->error);
                $stmt->close();
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Database insert error: " . $e->getMessage() . " | Query: " . $sql);
            return false;
        }
    }
    
    /**
     * Update data and return affected rows
     */
    public function update($sql, $params = []) {
        try {
            $this->last_query = $sql;
            $this->query_count++;
            
            $stmt = $this->prepare($sql, $params);
            if (!$stmt) {
                return false;
            }
            
            // Bind parameters if any
            if (!empty($stmt->query_params)) {
                if (!$this->bindParams($stmt, $stmt->query_params)) {
                    return false;
                }
            }
            
            if ($stmt->execute()) {
                $affected_rows = $stmt->affected_rows;
                $stmt->close();
                
                if (APP_DEBUG) {
                    error_log("Update successful. Affected rows: " . $affected_rows);
                }
                
                return $affected_rows;
            } else {
                error_log("Update execute failed: " . $stmt->error);
                $stmt->close();
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Database update error: " . $e->getMessage() . " | Query: " . $sql);
            return false;
        }
    }
    
    /**
     * Delete data and return affected rows
     */
    public function delete($sql, $params = []) {
        try {
            $this->last_query = $sql;
            $this->query_count++;
            
            $stmt = $this->prepare($sql, $params);
            if (!$stmt) {
                return false;
            }
            
            // Bind parameters if any
            if (!empty($stmt->query_params)) {
                if (!$this->bindParams($stmt, $stmt->query_params)) {
                    return false;
                }
            }
            
            if ($stmt->execute()) {
                $affected_rows = $stmt->affected_rows;
                $stmt->close();
                
                if (APP_DEBUG) {
                    error_log("Delete successful. Affected rows: " . $affected_rows);
                }
                
                return $affected_rows;
            } else {
                error_log("Delete execute failed: " . $stmt->error);
                $stmt->close();
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Database delete error: " . $e->getMessage() . " | Query: " . $sql);
            return false;
        }
    }
    
    /**
     * Count rows
     */
    public function count($table, $where = '', $params = []) {
        try {
            $sql = "SELECT COUNT(*) as count FROM " . $table;
            if (!empty($where)) {
                $sql .= " WHERE " . $where;
            }
            
            $result = $this->fetch($sql, $params);
            return $result ? (int)$result['count'] : 0;
            
        } catch (Exception $e) {
            error_log("Database count error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Check if record exists
     */
    public function exists($table, $where, $params = []) {
        try {
            $sql = "SELECT 1 FROM " . $table . " WHERE " . $where . " LIMIT 1";
            $result = $this->fetch($sql, $params);
            return !empty($result);
            
        } catch (Exception $e) {
            error_log("Database exists error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get paginated results
     */
    public function paginate($sql, $params = [], $page = 1, $limit = 10) {
        try {
            // Calculate offset
            $offset = ($page - 1) * $limit;
            
            // Get total count first
            $count_sql = "SELECT COUNT(*) as total FROM (" . $sql . ") as count_table";
            $count_result = $this->fetch($count_sql, $params);
            $total = $count_result ? (int)$count_result['total'] : 0;
            
            // Add LIMIT to original query
            $paginated_sql = $sql . " LIMIT ? OFFSET ?";
            $paginated_params = array_merge($params, [$limit, $offset]);
            
            $data = $this->fetchAll($paginated_sql, $paginated_params);
            
            return [
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit),
                'has_next' => $page < ceil($total / $limit),
                'has_prev' => $page > 1
            ];
            
        } catch (Exception $e) {
            error_log("Database paginate error: " . $e->getMessage());
            return [
                'data' => [],
                'total' => 0,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => 0,
                'has_next' => false,
                'has_prev' => false
            ];
        }
    }
    
    /**
     * Prepare statement with proper parameter binding
     */
    private function prepare($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt === false) {
            error_log("Prepare failed: " . $this->conn->error . " for query: " . $sql);
            return false;
        }
        
        if (!empty($params)) {
            // Store the statement with params for binding later
            $stmt->query_params = $params;
        }
        
        return $stmt;
    }
    
    /**
     * Bind parameters to prepared statement using persistent storage
     */
    private function bindParams($stmt, $params) {
        if (empty($params)) {
            return true;
        }
        
        // Auto-detect types
        $types = '';
        $values = [];
        
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $param;
        }
        
        // Store in object to keep references alive throughout execution
        $this->stmt_params = [];
        foreach ($values as $val) {
            $this->stmt_params[] = $val;
        }
        
        // Build refs array - CRITICAL: refs must point to stored values
        $bind_array = [$types];
        foreach ($this->stmt_params as $key => &$val) {
            $bind_array[] = &$val;
        }
        
        // Call bind_param with all arguments
        if (!call_user_func_array([$stmt, 'bind_param'], $bind_array)) {
            error_log("Bind param failed: " . $stmt->error . " | Types: " . $types . " | Param count: " . count($this->stmt_params));
            return false;
        }
        
        return true;
    }
    
    /**
     * Simple query - supports both with and without parameters
     */
    public function query($sql, $params = []) {
        $this->last_query = $sql;
        $this->query_count++;
        
        // Detect if this is a SELECT query
        $trimmed_sql = trim($sql);
        $is_select = stripos($trimmed_sql, 'SELECT') === 0;
        
        // If no parameters, use simple query
        if (empty($params)) {
            $result = $this->conn->query($sql);
            if ($result === false) {
                error_log("Query failed: " . $this->conn->error . " for query: " . $sql);
                return false;
            }
            return $result;
        }
        
        // If parameters provided, use prepared statement
        $stmt = $this->prepare($sql, $params);
        if (!$stmt) {
            return false;
        }
        
        // Bind parameters
        if (!$this->bindParams($stmt, $stmt->query_params)) {
            return false;
        }
        
        if (!$stmt->execute()) {
            error_log("Query execute failed: " . $stmt->error . " for query: " . $sql);
            return false;
        }
        
        // For SELECT queries, return result set
        // For INSERT/UPDATE/DELETE, return true
        if ($is_select) {
            $result = $stmt->get_result();
            $stmt->close();
            return $result;
        } else {
            $stmt->close();
            return true;
        }
    }
    
    /**
     * Check if table exists
     */
    public function tableExists($tableName) {
        $result = $this->query("SHOW TABLES LIKE '" . $this->conn->real_escape_string($tableName) . "'");
        return $result && $result->num_rows > 0;
    }
    
    /**
     * Get table structure
     */
    public function getTableStructure($tableName) {
        return $this->fetchAll("DESCRIBE " . $this->conn->real_escape_string($tableName));
    }
    
    /**
     * Transaction Methods
     */
    public function beginTransaction() {
        if (!$this->transaction_started) {
            $this->conn->autocommit(false);
            $this->transaction_started = true;
            
            if (APP_DEBUG) {
                error_log("Transaction started");
            }
            
            return true;
        }
        return false;
    }
    
    public function commit() {
        if ($this->transaction_started) {
            $result = $this->conn->commit();
            $this->conn->autocommit(true);
            $this->transaction_started = false;
            
            if (APP_DEBUG) {
                error_log("Transaction committed");
            }
            
            return $result;
        }
        return false;
    }
    
    public function rollback() {
        if ($this->transaction_started) {
            $result = $this->conn->rollback();
            $this->conn->autocommit(true);
            $this->transaction_started = false;
            
            if (APP_DEBUG) {
                error_log("Transaction rolled back");
            }
            
            return $result;
        }
        return false;
    }
    
    /**
     * Utility Methods
     */
    public function getLastInsertId() {
        return $this->conn->insert_id;
    }
    
    public function getAffectedRows() {
        return $this->conn->affected_rows;
    }
    
    public function getLastQuery() {
        return $this->last_query;
    }
    
    public function getQueryCount() {
        return $this->query_count;
    }
    
    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }
    
    /**
     * Health Check
     */
    public function ping() {
        return $this->conn->ping();
    }
    
    public function getServerInfo() {
        return $this->conn->server_info;
    }
    
    public function getConnectionId() {
        return $this->conn->thread_id;
    }
    
    /**
     * Debug Methods
     */
    public function getDebugInfo() {
        return [
            'host' => DB_HOST,
            'database' => DB_NAME,
            'charset' => $this->conn->character_set_name(),
            'server_info' => $this->conn->server_info,
            'connection_id' => $this->conn->thread_id,
            'query_count' => $this->query_count,
            'last_query' => $this->last_query,
            'transaction_active' => $this->transaction_started
        ];
    }
    
    /**
     * Backup Methods (Simple)
     */
    public function backupTable($tableName, $filePath = null) {
        try {
            if (!$filePath) {
                $filePath = ROOT_PATH . "/backups/" . $tableName . "_" . date('Y-m-d_H-i-s') . ".sql";
            }
            
            // Create backup directory if it doesn't exist
            $backupDir = dirname($filePath);
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            // Get table structure
            $structure = $this->query("SHOW CREATE TABLE " . $this->escape($tableName));
            if (!$structure) {
                return false;
            }
            
            $createTable = $structure->fetch_array();
            $sql = $createTable[1] . ";\n\n";
            
            // Get table data
            $data = $this->fetchAll("SELECT * FROM " . $this->escape($tableName));
            
            foreach ($data as $row) {
                $values = array_map(function($value) {
                    return $value === null ? 'NULL' : "'" . $this->escape($value) . "'";
                }, $row);
                
                $sql .= "INSERT INTO " . $tableName . " VALUES (" . implode(', ', $values) . ");\n";
            }
            
            return file_put_contents($filePath, $sql);
            
        } catch (Exception $e) {
            error_log("Backup error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cleanup and destructor
     */
    public function __destruct() {
        if ($this->transaction_started) {
            $this->rollback();
        }
        
        if ($this->conn) {
            $this->conn->close();
        }
        
        if (APP_DEBUG) {
            error_log("Database connection closed. Total queries: " . $this->query_count);
        }
    }
}
?>