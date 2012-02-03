<?php

include 'inflector.php';

/**
 * BaseModel - The base class from which all models descend.
 */
abstract class BaseModel {
//  public $attributes;
  static $relations = array();
  static $validators = array();
  
  public $hiddenKeys = array('_data', '_errors', 'hiddenKeys', 'dbh', 'tableName', 'inDatabase', 'idField');
  public $_errors = array();
  public $_data = array();
  public $inDatabase = false;
  public $dbh;
  public $tableName;
  public $idField;
  
  function __id() { return $this->{$this->idField}; }
  
  function __construct($params = array()) {
    global $database;
    $this->dbh = $database;
    $this->tableName = underscorize(pluralize(get_class($this)));
    $this->idField = underscorize(singularize(get_class($this)) . 'Id');
    
    foreach ($params as $key => $value) {
      $this->$key = $value;
    }
  }
  
  /**
   * Gets an array of the values set on this object.
   * @return an array of key-value pairs.
   */
  function values() {
    $class = get_class($this);
    $relations =& self::$relations[$class];
    foreach ($relations as $key => $relation) {
      array_push($this->hiddenKeys, "_$key");
    }
    $this->hiddenKeys = array_unique($this->hiddenKeys );
    
    return array_diff_key(get_object_vars($this), array_flip($this->hiddenKeys));
  }
  
  /**
   * Determines whether any of the fields on this object have changed since it was last saved.
   * @return true if any of the fields have changed.
   */
  function hasChanges() {
    $class = get_class($this);
    $relations =& self::$relations[$class];
    foreach ($relations as $key => $relation) {
      $cache = "_$key";
      if ($relation['arity'] == 'hasOne' && $this->$cache && $this->$cache->hasChanges()) {
        return true;
      } elseif ($relation['arity'] == 'hasMany' && $this->$cache) {
        foreach ($this->$cache as $val) {
          if ($val->hasChanges()) {
            return true;
          }
        }
      }
    }
    
    return array_intersect_key($this->values(), $this->_data) != $this->_data;
  }
  
  /**
   * Performs validation on the object.
   * @return true if the object is valid.
   */
  function isValid() {
    $class = get_class($this);
    $validators = get_class_vars($class);
    $validators = $validators['validators'][$class];
    if ($validators) {
      foreach ($validators as $field => $field_validators) {
        if ($field_validators) {
          foreach ($field_validators as $fv) {
            call_user_func(array($class, $fv['type']), $this, $field, $this->$field, $fv['options']);
          }
        }
      }
    }
    return !$this->errors();
  }
  
  /**
   * INSERTs this object in the database.
   * @return true if the INSERT succeeded.
   */
  function create() {
    $this->beforeCreate();
    
    if ($this->isValid()) {
      $this->inDatabase = !!($this->dbh->insert($this->tableName, $this->values()));
      $this->{$this->idField} = mysql_insert_id();
      $this->_data[$this->idField] = mysql_insert_id();
    
      $class = get_class($this);
      $relations =& self::$relations[$class];
      foreach ($relations as $key => $relation) {
        $cache = "_$key";
        if ($relation['arity'] == 'hasOne' && $this->$cache) {
          $this->$key = $this->$cache; // update the IDs on the relations
          $this->$cache->save();
        } elseif ($relation['arity'] == 'hasMany' && $this->$cache) {
          foreach ($this->$cache as $val) {
            $val->{$this->idField} = $this->__id();
            $val->save();
          }
        }
      }
    }
    $this->afterCreate();
    
    return $this->inDatabase;
  }
  function beforeCreate() {}
  function afterCreate() {}
  
  /**
   * UPDATEs this object in the database.
   */
  function update() {
    if ($this->isValid()) {
      if ($this->inDatabase) {
        $this->beforeUpdate();
        $result = $this->dbh->update(
          $this->tableName,
          array_intersect_key($this->values(), $this->_data),
          $this->idField . ' = ' . massage_db_value($this->__id())
        );
        if ($result) {
          $this->_data = $this->values();
        }
        $this->afterUpdate();
      }
    
      $class = get_class($this);
      $relations =& self::$relations[$class];
      foreach ($relations as $key => $relation) {
        $cache = "_$key";
        if ($relation['arity'] == 'hasOne' && $this->$cache) {
          $this->$cache->save();
        } elseif ($relation['arity'] == 'hasMany' && $this->$cache) {
          foreach ($this->$cache as $val) {
            $val->save();
          }
        }
      } 
    
      return $result;
    }
  }
  function beforeUpdate() {}
  function afterUpdate() {}
  
  /**
   * A generic call for persisting a record; delegates to #create and #update as appropriate.
   */
  function save() {
    $this->beforeSave();
    if ($this->inDatabase) {
      $result = $this->update();
    } else {
      $result = $this->create();
    }
    $this->afterSave();
    return $result;
  }
  function beforeSave() {}
  function afterSave() {}
  
  /**
   * DELETEs the object from the database.
   */
  function destroy() {
    if ($this->inDatabase) {
      $this->beforeDestroy();
      $result = $this->dbh->delete($this->tableName, $this->idField . ' = ' . $this->__id());
      $this->afterDestroy();
    }
    return $result;
  }
  function beforeDestroy() {}
  function afterDestroy() {}
  
  /**
   * Reverts any changes made to this object since it was fetched.
   */
  function reset() {
    $values = $this->_data;
    foreach ($values as $key => $value) {
      $this->$key = $value;
    }
  }
  
  /**
   * Declares the given field as being invalid.  The message should unambiguously describe the error.
   */
  function error($field, $msg) {
    $errors =& $this->_errors[$field];
    if (!$errors) { $errors = array(); }
    array_push($errors, $msg);
  }
  
  /**
   * Checks to see if the given field has any errors associated with it.
   * @return true if the field has errors.
   */
  function errorOn($field) {
    return count($this->_errors[$field]);
  }
  
  /**
   * Collects the error messages in an array.
   * @return an array of error messages.
   */
  function errors() {
    $msg = array();
    foreach ($this->_errors as $field => $errors) {
      foreach ($errors as $error) {
        array_push($msg, $error);
      }
    }
    return $msg;
  }
  
  function __get($property) {
    $class = get_class($this);
    $relations =& self::$relations[$class];
    if (!$relations) { $relations = array(); }
    $relation = $relations[$property];
    if ($relation) {
      $class = $relation['className'];
      $cache = "_$property";
      
      if (!$this->$cache) {
        if ($relation['arity'] == 'belongsTo') {
          $obj = new $class();
          $key   = $relation['keyField'] ? $relation['keyField'] : $obj->idField;
          $result = $this->dbh->findWhere($class, "$key = " . massage_db_value($this->$key));
        } else {
            $key   = $relation['keyField'] ? $relation['keyField'] : $this->idField;
          $result = $this->dbh->findWhere($class, "$key = " . massage_db_value($this->__id()));
        }
        
        if ($relation['arity'] != 'hasMany') {
          $result = count($result) ? $result[0] : null;
        } else {
          $result = $result ? $result : array();
        }
        
        $this->$cache = $result;
      }
      
      return $this->$cache;
    } else {
      return $this->$property;
    }
  }
  
  function __set($property, $value) {
    $class = get_class($this);
    $relations =& self::$relations[$class];
    if (!$relations) { $relations = array(); }
    $relation = $relations[$property];
    if ($relation) {
      $key   = $relation['keyField'] ? $relation['keyField'] : $this->idField;
      $cache = "_$property";
      if ($relation['arity'] == 'hasOne') {
        if ($value) {
          $value->$key = $this->__id();
        }
        // TODO: Nullify?
      } elseif ($relation['arity'] == 'hasMany') {
        if ($value && is_array($value)) {
          foreach($value as $val) {
            if ($val) {
              $val->$key = $this->__id();
            }
          }
        } elseif ($value) {
          die("Assigning a single value to a hasMany relationship");
        }
        // TODO: Nullify?
      } else {
        $obj = new $relation['className']();
        $key = $relation['keyField'] ? $relation['keyField'] : $obj->idField;
        $this->$key = $value->$key;
      }
      $this->$cache = $value;
      return $this->$cache;
    } else {
      $this->$property = $value;
    }
  }
  
  /***********\
  | Relations |
  \***********/
  
  /**
   * Used for setting up a one-to-one relationship.  Assumes the foreign key is on the distant table.
   * @return An object that #belongsTo this object.
   */
  static function hasOne($name, $options = array()) {
    $class = get_static_class();
    $relations =& self::$relations[$class];
    if (!$relations) { $relations = array(); }
    $relations[$name] = array(
      'arity' => 'hasOne',
      'className' => $options['className']
        ? $options['className']
        : preg_replace("/ |_/", '', ucwords($name)),
      'keyField' => $options['keyField']
        ? $options['keyField']
        : null,
    );
  }
  
  /**
   * Used for setting up a many-to-one relationship.
   * @return An array of objects that #belongsTo this object.
   */
  static function hasMany($name, $options = array()) {
    $class = get_static_class();
    $relations =& self::$relations[$class];
    if (!$relations) { $relations = array(); }
    $relations[$name] = array(
      'arity' => 'hasMany',
      'className' => $options['className']
        ? $options['className']
        : preg_replace("/ |_/", '', ucwords(singularize($name))),
      'keyField' => $options['keyField']
        ? $options['keyField']
        : null,
    );
  }
  
  /**
   * Used for setting up a one-to-any relationship.  Assumes the foreign key is on the local table.
   * @return The object this object belongs to.
   */
  static function belongsTo($name, $options = array()) {
    $class = get_static_class();
    $relations =& self::$relations[$class];
    if (!$relations) { $relations = array(); }
    $relations[$name] = array(
      'arity' => 'belongsTo',
      'className' => $options['className']
        ? $options['className']
        : preg_replace("/ |_/", '', ucwords($name)),
      'keyField' => $options['keyField']
        ? $options['keyField']
        : null,
    );
  }
  
  /************\
  | Validation |
  \************/
  
  static function validate($type, $field, $options = array()) {
    $class = get_static_class();
    $validators =& self::$validators[$class];
    if (!$validators) { $validators = array(); }
    $field_validators =& $validators[$field];
    if (!$field_validators) { $field_validators = array(); }
    array_push($field_validators, array(
      'type' => $type,
      'options' => $options,
    ));
  }
  
  static function required($obj, $field, $value, $options) {
    $message = $options['message'];
    if (!$message) { $message = ucwords($field) . " is required."; }
    if (!$value) { $obj->error($field, $message); }
  }
  
  static function unique($obj, $field, $value, $options) {
    global $database;
    $message = $options['message'];
    if (!$message) { $message = ucwords($field) . " '$value' has already exists; please choose another."; }
    $find = "find_by_$field";
    if ($database->$find(get_class($obj), $value)) { $obj->error($field, $message); }
  }
}

?>