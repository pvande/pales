<?php

class DatabaseConnection {
  function __construct($config) {
    ini_set('sql.safe_mode', 0);
    $this->dbh = mysql_connect(
      $config[APP_ENV]['host'],
      $config[APP_ENV]['user'],
      $config[APP_ENV]['password']
    );
    $this->database = $config[APP_ENV]['database'];
    mysql_select_db($this->database, $this->dbh) or die("Could not connect to database");
  }
  
  function __destruct() {
    mysql_close($this->dbh);
  }
  
  function _query($query) {
    $start = microtime();
    $result = mysql_query($query, $this->dbh);
    $time = number_format(microtime() - $start, 3);
    
    return $result;
  }
  
  /**
   * Inserts the given values into the given table.
   * @return the number of rows inserted.
   */
  function insert($table, $values_array) {
    if (!is_array($values_array)) { return 0; }
    $columns = array();
    $values = array();
    
    if (is_associative_array($values_array)) {
      // If the data is an associative array, we'll assume that the keys
      // are meant to represent the column names, and the values are the
      // corresponding values for the database.
      while (list($column, $value) = each($values_array)) {
        array_push($columns, $column);
        array_push($values, massage_db_value($value));
      }
      $columns = "(" . join(", ", $columns) . ")";
    } elseif (is_array($values_array[0])) {
      // If the first element of the array is an array itself, we'll presume
      // that the structure is an array of arrays, an recursively process it.
      $results = 0;
      foreach ($values_array as $value) {
        $results += $this->insert($table, $value);
      }
      return $results;
    } else {
      // If the data is a simple indexed array, we'll assume that it represents
      // a set of ordered values to insert.
      $columns = '';
      $values = array_map('massage_db_value', $values_array);
    }
    $values = "(" . join(", ", $values) . ")";
    
    $this->_query("INSERT INTO $table $columns VALUES $values");
    return mysql_affected_rows();
  }
  
  /**
   * Updates the given columns (keys) with the given values on the given table.
   * Takes an optional condition string.
   * @return the number of affected rows.
   */
  function update($table, $values, $condition = '1 = 1') {
    $setValues = array();
    while (list($key, $val) = each($values)) {
      array_push($setValues, "$key = " . massage_db_value($val));
    }
    $setValues = join(", ", $setValues);
    $this->_query("UPDATE $table SET $setValues WHERE $condition");
    return mysql_affected_rows();
  }

  /**
   * Selects the given columns from the given table.
   * Takes an optional condition string, order by array, and group by array.
   * @return a result resource; not fit for human consumption!
   */
  function select($table, $columns = '*', $where = '', $order = '', $group = '') {
    if ($where) {
      $where = "WHERE $where";
    }
    if ($order) {
      $order = join(", ", $order);
      $order = "ORDER BY $order";
    }
    if ($group) {
      $group = join(", ", $group);
      $group = "GROUP BY $group";
    }
    if (is_array($columns)) {
      $columns = join(", ", $columns);
    }
    
    return $this->_query("SELECT $columns FROM $table $where $order $group");
  }
  
  /**
   * Deletes rows from the given table.
   * Takes a *mandatory* condition string.
   * @return the number of deleted rows.
   */
  function delete($table, $where) {
    $this->_query("DELETE FROM $table WHERE $where");
    return mysql_affected_rows();
  }

  /**
   * Finds the record with the specified ID in the database, and instantiate the given class around it.
   * @return the object with the given ID.
   */
   function find($class, $id) {
     $obj = new $class();
     $result = $this->findWhere($class, $obj->idField . ' = ' . massage_db_value($id));
     return $result ? $result[0] : null;
   }
   
   /**
    * Finds all the records matching the given where clause, and instantiates the given class around them.
    * @return an array of the objects matching the condition.
    */
  function findWhere($class, $condition) {
    $obj = new $class();
    $result = $this->select($obj->tableName, '*', $condition);
    $results = array();
    while ($result && $obj = mysql_fetch_object($result, $class)) {
      $obj->_data = $obj->values();
      $obj->inDatabase = true;
      array_push($results, $obj);
    }
    return $results;
  }
  
  function __call($method, $params) {
    if (preg_match("/^findBy|find_by_/", $method)) {
      $method = underscorize($method);
      $conditions = preg_replace("/^find_by_/", '', $method);
      $conditions = preg_split("/_and_/", $conditions);
      $class = array_shift($params);
      
      if ($class && count($conditions) == count($params)) {
        $where = '1 = 1';
        foreach ($conditions as $condition) {
          $where = $where . " AND $condition = " . massage_db_value(array_shift($params));
        }
        return $this->findWhere($class, $where);
      } else {
        die("Improper use of '$method'");
      }
    } else {
      die("Called non-existent method '$method'");
    }
  }
}

?>