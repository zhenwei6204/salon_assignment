<?php
require_once ROOT_PATH . '/utils/ORM.php';
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel {
    protected $orm;
    protected $data = [];

    public function __construct() {
        $this->orm = new ORM(get_called_class());
    }

    public function __get($property) {
        return $this->data[$property] ?? null;
    }

    public function __set($property, $value) {
        $this->data[$property] = $value;
    }

    public function save() {
        return $this->orm->save($this, $this->data);
    }

    public static function find($id) {
        $instance = new static();
        $data = $instance->orm->find($id);
        if ($data) {
            $instance->data = $data;
            return $instance;
        }
        return null;
    }

    public static function findAll($conditions = [], $orderBy = null, $limit = null) {
        $instance = new static();
        $results = $instance->orm->findAll($conditions, $orderBy, $limit);
        $objects = [];
        
        foreach ($results as $data) {
            $obj = new static();
            $obj->data = $data;
            $objects[] = $obj;
        }
        
        return $objects;
    }

    public static function count($conditions = []) {
        $instance = new static();
        return $instance->orm->count($conditions);
    }

    public function delete() {
        if ($this->id) {
            return $this->orm->delete($this->id);
        }
        return false;
    }

    public function toArray() {
        return $this->data;
    }

    public function fill($data) {
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
        return $this;
    }

    protected function executeQuery($query, $params = []) {
        return $this->orm->executeQuery($query, $params);
    }
}