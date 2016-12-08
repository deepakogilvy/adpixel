<?php
App::uses('AppModel', 'Model');
App::uses('Entity', 'Model');
App::uses('Hash', 'Utility');

class Table extends AppModel {
    public $entity;
    protected $_entityClass = null;
    protected $_savedEntityStates = [];

    public function __construct($id = false, $table = null, $ds = null) {
        if (is_array($id)) {
            $alias = Hash::get($id, 'alias') ?: (Hash::get($id, 'table') ?: $this->alias);
            $id['alias'] = Inflector::singularize(preg_replace('/Table$/', '', $alias));
            $this->name = $this->alias = $this->alias($id['alias']);
            $schema = Hash::get($id, 'schema');

            if ($schema !== null) {
                $this->_schema = $schema;
            }
        }

        if ($table === null) {
            if ($this->name === null) {
                $this->name = (isset($name) ? $name : get_class($this));
            }

            if ($this->alias === null) {
                $this->alias = (isset($alias) ? $alias : $this->name);
            }
            $table = Inflector::tableize(preg_replace('/Table$/', '', $this->alias));
        }

        parent::__construct($id, $table, $ds);
        $this->entityClass(Inflector::singularize($this->name) . 'Entity');
        $this->initialize([]);
        $this->entity(false);
    }

    public static function defaultConnectionName() {
        return 'default';
    }

    public function initialize(array $config) {
    }

    public function table($table = null) {
        if ($table !== null) {
            $this->table = $this->useTable = $table;
        }

        if ($this->table === null) {
            $table = get_class($this);
            $table = substr(end($table), 0, -5);
            if (empty($table)) {
                $table = $this->alias();
            }
            $this->table = Inflector::underscore($table);
        }

        return $this->table;
    }

    public function alias($alias = null) {
        if ($alias !== null) {
            $this->_alias = $alias;
        }
        if ($this->_alias === null) {
            $alias = get_class($this);
            $this->_alias = Inflector::singularize(preg_replace('/Table$/', '', $alias));
        }
        return $this->_alias;
    }

    protected function _initializeSchema(Schema $table) {
        throw new Exception("Method '_initializeSchema' not implemented");
    }

    public function connection($conn = null) {
        if ($conn === null) {
            return $this->getDataSource();
        }

        $this->setDataSource($conn);
        return $this->getDataSource();
    }

    public function entity($boolean = null) {
        if ($boolean !== null) {
            $this->entity = $boolean;
        }

        return $this->entity;
    }

    public function primaryKey($key = null) {
        if ($key !== null) {
            $this->primaryKey = $key;
        }

        return $this->primaryKey;
    }

    public function displayField($key = null) {
        if ($key !== null) {
            $this->displayField = $key;
        }

        return $this->displayField;
    }

    public function entityClass($name = null) {
        if ($name === null && !$this->_entityClass) {
            $name = Inflector::classify($this->alias()) . 'Entity';
        }

        if ($name !== null) {
            App::uses($name, $this->_EntityClassLocation());
            $this->_entityClass = $name;
        }

        return $this->_entityClass;
    }

    public function addBehavior($name, $options = []) {
        $this->Behaviors->load($name, $options);
    }

    public function removeBehavior($name) {
        $this->Behaviors->unload($name);
    }

    public function behaviors() {
        $this->Behaviors;
    }

    public function hasBehavior($name) {
        $this->Behaviors->loaded($name);
    }

    public function association($name) {
        throw new Exception("Method 'association' not implemented");
    }

    public function belongsTo($associated, array $options = []) {
        $this->_bindModel('belongsTo', $associated, $options);
    }

    public function hasOne($associated, array $options = []) {
        $this->_bindModel('hasOne', $associated, $options);
    }

    public function hasMany($associated, array $options = []) {
        $this->_bindModel('hasMany', $associated, $options);
    }

    public function belongsToMany($associated, array $options = []) {
        $this->_bindModel('hasAndBelongsToMany', $associated, $options);
    }

    protected function _bindModel($type, $associated, array $options = []) {
        $reset = isset($options['reset']) ? $options['reset'] : false;
        if (isset($options['reset'])) {
            unset($options['reset']);
        }

        return $this->bindModel([
            $type => [$associated => $options]
        ], $reset);
    }

    public function get($primaryKey, $options = []) {
        $key = (array)$this->primaryKey();
        $conditions = array_combine($key, (array)$primaryKey);
        if (!isset($options['conditions'])) {
            $options['conditions'] = [];
        }
        $options['conditions'] = array_merge($options['conditions'], $conditions);
        $entity = $this->find('first', $options);

        if (!$entity) {
            throw new NotFoundException(sprintf(
                'Record "%s" not found in table "%s"',
                implode(',', (array)$primaryKey),
                $this->table()
            ));
        }

        return $entity;
    }

    public function validationDefault(Validator $validator) {
        throw new Exception("Method 'validationDefault' not implemented");
    }

    public function exists($conditions = null) {
        if (!is_array($conditions)) {
            return parent::exists($conditions);
        }

        return (bool)$this->find('count', [
            'conditions' => $conditions,
            'recursive' => -1,
            'callbacks' => false
        ]);
    }

    public function save($entity = null, $validate = true, $fieldList = []) {
        if (!is_object($entity) || !($entity instanceof $entity)) {
            $success = parent::save($entity, $validate, $fieldList);
            if (!$success) {
                return false;
            }

            $entity = $this->newEntity($success);
            $entity->isNew(false);
            return $entity;
        }

        if ($entity->isNew() === false && !$entity->dirty()) {
            return $entity;
        }

        if ($this->id != $entity->get($this->primaryKey)) {
            $this->create();
        }

        if (!$entity->dirty()) {
            return false;
        }

        if ($entity->isNew() === false) {
            $entity->unsetProperty(['modified', 'updated']);
        } else {
            $entity->unsetProperty(['created', 'modified', 'updated']);
        }

        $success = parent::save($entity, $validate, $fieldList);
        $insertedId = $this->getInsertID();

        if (!$success) {
            return false;
        }

        if (is_array($success)) {
            $alias = $this->alias();
            $dateFields = ['created', 'modified', 'updated'];
            foreach ($dateFields as $field) {
                $value = Hash::get($success, "{$alias}.{$field}", null);
                if ($value !== null) {
                    $entity->set($field, $value);
                }
            }
        }

        $entity->isNew(false);

        if (!empty($insertedId)) {
            $entity->set($this->primaryKey(), $this->getInsertID());
        }

        return $entity;
    }

    public function marshaller($safe = false) {
        throw new Exception("Method 'marshaller' not implemented");
    }

    public function entityValidator() {
        throw new Exception("Method 'entityValidator' not implemented");
    }

    public function newEntity(array $data, $associations = null, $useSetters = true) {
        if ($associations !== null) {
            throw new Exception("Method 'newEntity' not fully implemented");
        }

        $class = $className = $this->entityClass();

        if (!class_exists($class)) {
            App::uses($class, $this->_EntityClassLocation());
            if (!class_exists($class)) {
                $class = 'Entity';
            }
        }

        $alias = $this->alias();
        if (!empty($data[$alias]) && !isset($data[$alias][0])) {
            $entityData = $data[$alias];
            foreach ($data as $key => $value) {
                if ($key != $alias) {
                    $entityData[$key] = $value;
                }
            }
            $data = $entityData;
        }

        $entity = new $class($data, [
            'className' => $className,
            'useSetters' => $useSetters,
        ]);
        return $entity;
    }

    public function newEntities(array $data, $associations = null) {
        throw new Exception("Method 'newEntities' not implemented");
    }

    public function convertToEntity($data) {
        if (is_null($data) || empty($data[$this->alias][$this->primaryKey])) {
            return null;
        }

        $entity = $this->newEntity($data, null, false);
        $entity->isNew(false);
        $entity->clean();
        return $entity;
    }

    public function convertToEntities($list) {
        if ($list && !Hash::numeric(array_keys($list))) {
            return $this->convertToEntity($list);
        }

        $result = [];
        foreach ($list as $data) {
            $result[] = $this->convertToEntity($data);
        }
        return $result;
    }

    public function beforeFind($query) {
        $this->_saveEntityState();

        if (isset($query['entity'])) {
            $this->entity = $query['entity'];
        }

        return parent::beforeFind($query);
    }

    public function afterFind($results, $primary = false) {
        $results = parent::afterFind($results, $primary);

        if ($this->entity && $primary && is_array($results)) {
            $results = $this->convertToEntities($results);
        }

        $this->_restoreEntityState();
        return $results;
    }

    protected function _saveEntityState() {
        $this->_savedEntityStates[] = $this->entity;
    }

    protected function _restoreEntityState() {
        $this->entity = array_pop($this->_savedEntityStates);
    }

    protected function _entityClassForData($data) {
        return $this->entityClass();
    }

    public function allEntities($params = []) {
        $params['entity'] = true;
        return $this->find('all', $params);
    }

    public function entities($params = []) {
        return $this->allEntities($params);
    }

    public function __call($method, $params) {
        list($entity, $method) = $this->_analyzeMethodName($method);

        $return = parent::__call($method, $params);

        if ($entity && !is_null($return)) {
            $return = $this->convertToEntities($return);
        }
        return $return;
    }

    protected function _analyzeMethodName($method) {
        $entity = false;

        if (preg_match('/^(entity|(?:all)?entities)by(.+)$/i', $method, $matches)) {
            $entity = true;
            $all = (strtolower($matches[1]) != 'entity');
            $method = ($all ? 'findAllBy' : 'findBy') . $matches[2];
        }

        return [$entity, $method];
    }

    public function set($one, $two = null) {
        if ($one instanceof Entity) {
            $one = $one->toArray();
        }
        return parent::set($one, $two);
    }

    protected function _entityClassLocation() {
        $location = 'Model/Entity';
        if ($this->plugin !== null) {
            $location = $this->plugin . '.' . $location;
        }
        return $location;
    }

    protected function _findCount($state, $query, $results = []) {
        if ($state == 'before') {
            $this->_saveEntityState();
            $this->entity = false;
            return parent::_findCount($state, $query, $results);
        }

        $return = parent::_findCount($state, $query, $results);
        $this->_restoreEntityState();
        return $return;
    }

    protected function _findThreaded($state, $query, $results = []) {
        if ($state == 'before') {
            $this->_saveEntityState();
            $this->entity = false;
            return parent::_findThreaded($state, $query, $results);
        }

        $return = parent::_findThreaded($state, $query, $results);
        $this->_restoreEntityState();
        return $return;
    }

    public function __debugInfo() {
        $conn = $this->connection();
        return [
            'table' => $this->table(),
            'alias' => $this->alias(),
            'entityClass' => $this->entityClass(),
            'associations' => $this->_associations->keys(),
            'behaviors' => $this->behaviors()->loaded(),
            'defaultConnection' => $this->defaultConnectionName(),
            'connectionName' => $conn ? $conn->configName() : null
        ];
    }
}