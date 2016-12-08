<?php
App::uses('Hash', 'Utility');
App::uses('ModelValidator', 'Model');
App::uses('Inflector', 'Utility');

class Entity implements ArrayAccess, JsonSerializable {
    protected $_properties = [];
    protected $_hidden = [];
    protected $_virtual = [];
    protected $_className;
    protected $_dirty = [];
    protected static $_accessors = [];
    protected $_new = null;
    protected $_errors = [];
    protected $_accessible = [];

    public function __construct(array $properties = [], array $options = []) {
        $options += [
            'useSetters' => true,
            'markClean' => false,
            'markNew' => null,
            'guard' => false,
            'className' => null,
        ];

        if ($options['className']) {
            $this->_className = $options['className'];
        } else {
            $this->_className = get_class($this);
        }

        $this->set($properties, [ 'setter' => $options['useSetters'], 'guard' => $options['guard'] ]);

        if ($options['markClean']) {
            $this->clean();
        }

        if ($options['markNew'] !== null) {
            $this->isNew($options['markNew']);
        }
    }

    public function &__get($property) {
        return $this->get($property);
    }

    public function __set($property, $value) {
        $this->set($property, $value);
    }

    public function __isset($property) {
        return $this->has($property);
    }

    public function __unset($property) {
        $this->unsetProperty($property);
    }

    public function set($property, $value = null, $options = []) {
        if (is_string($property)) {
            $guard = false;
            $property = [$property => $value];
        } else {
            $guard = true;
            $options = (array)$value;
        }

        $options += ['setter' => true, 'guard' => $guard];

        $alias = $this->alias();
        if (isset($property[$alias])) {
            foreach ($property[$alias] as $key => $value) {
                $property[$key] = $value;
            }

            unset($property[$alias]);
        }

        foreach ($property as $p => $value) {
            if ($options['guard'] === true && !$this->accessible($p)) {
                continue;
            }
            if (ctype_upper($p[0]) && is_array($value)) {
                list($p, $value) = $this->_transformIntoEntity($p, $value);
            }

            $markDirty = true;
            if (isset($this->_properties[$p])) {
                $markDirty = $value !== $this->_properties[$p];
            }

            if ($markDirty) {
                $this->dirty($p, true);
            }

            if (!$options['setter']) {
                $this->_properties[$p] = $value;
                continue;
            }

            $setter = 'set' . Inflector::camelize($p);
            if ($this->_methodExists($setter)) {
                $value = $this->{$setter}($value);
            }
            $this->_properties[$p] = $value;
        }
        return $this;
    }

    protected function _transformIntoEntity($p, $value) {
        $_p = Inflector::singularize($p);
        $className = $_p . 'Entity';

        if (empty($value)) {
            return [$p, $value];
        }

        if (!class_exists($_p)) {
            App::uses($_p, 'Model');
            if (!class_exists($_p)) {
                return [$p, $value];
            }
        }

        if (!is_subclass_of($_p, 'Table')) {
            return [$p, $value];
        }

        if (!class_exists($className)) {
            App::uses($className, 'Model/Entity');
        }

        if (!class_exists($className)) {
            $className = 'Entity';
        }

        if (is_array($value) && Hash::numeric(array_keys($value))) {
            $_value = [];
            foreach ($value as $sub) {
                $_value[] = new $className($sub);
            }
            $value = $_value;
        } else {
            $value = new $className($value);
        }

        return [$p, $value];
    }

    public function &get($property) {
        $method = 'get' . Inflector::camelize($property);
        $value = null;

        if (isset($this->_properties[$property])) {
            $value =& $this->_properties[$property];
        }

        $methodExists = false;
        if ($this->_methodExists($method)) {
            $methodExists = true;
            $value = $this->{$method}($value);
        }

        return $value;
    }

    public function has($property) {
        return $this->get($property) !== null;
    }

    public function unsetProperty($property) {
        $property = (array)$property;
        foreach ($property as $p) {
            unset($this->_properties[$p]);
        }

        return $this;
    }

    public function hiddenProperties($properties = null) {
        if ($properties === null) {
            return $this->_hidden;
        }
        $this->_hidden = $properties;
        return $this;
    }

    public function virtualProperties($properties = null) {
        if ($properties === null) {
            return $this->_virtual;
        }
        $this->_virtual = $properties;
        return $this;
    }

    public function visibleProperties() {
        $properties = array_keys($this->_properties);
        $properties = array_merge($properties, $this->_virtual);
        return array_diff($properties, $this->_hidden);
    }

    public function toArray() {
        $alias = $this->alias();
        $result = [$alias => []];

        foreach ($this->visibleProperties() as $property) {
            $value = $this->get($property);
            if (is_array($value) && isset($value[0]) && $value[0] instanceof self) {
                $result[$property] = [];
                foreach ($value as $k => $entity) {
                    $result[$property][$k] = $entity->toArray()[$entity->alias()];
                }
            } elseif ($value instanceof self) {
                $result[$property] = $value->toArray()[$value->alias()];
            } else {
                $result[$alias][$property] = $value;
            }
        }
        if (empty($result[$alias])) {
            unset($result[$alias]);
        }
        return $result;
    }

    public function jsonSerialize() {
        return $this->toArray();
    }

    public function offsetExists($offset) {
        return $this->has($offset);
    }

    public function &offsetGet($offset) {
        return $this->get($offset);
    }

   public function offsetSet($offset, $value) {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset) {
        $this->unsetProperty($offset);
    }

    protected function _methodExists($method) {
        if (empty(static::$_accessors[$this->_className])) {
            static::$_accessors[$this->_className] = array_flip(get_class_methods($this));
        }
        return isset(static::$_accessors[$this->_className][$method]);
    }

    public function extract(array $properties, $onlyDirty = false) {
        $result = [];
        foreach ($properties as $property) {
            if (!$onlyDirty || $this->dirty($property)) {
                $result[$property] = $this->get($property);
            }
        }
        return $result;
    }

    public function dirty($property = null, $isDirty = null) {
        if ($property === null) {
            return !empty($this->_dirty);
        }

        if ($isDirty === null) {
            return isset($this->_dirty[$property]);
        }

        if (!$isDirty) {
            unset($this->_dirty[$property]);
            return false;
        }

        $this->_dirty[$property] = true;
        unset($this->_errors[$property]);
        return true;
    }

    public function clean() {
        $this->_dirty = [];
        $this->_errors = [];
    }

    public function isNew($new = null) {
        if ($new === null) {
            return $this->_new;
        }
        return $this->_new = (bool)$new;
    }

    public function validate(ModelValidator $validator) {
        throw new Exception("Missing implementation for 'validate()' method");
    }

    public function errors($field = null, $errors = null) {
        if ($field === null) {
            return $this->_errors;
        }

        if (is_string($field) && $errors === null) {
            $errors = isset($this->_errors[$field]) ? $this->_errors[$field] : [];
            if (!$errors) {
                $errors = $this->_nestedErrors($field);
            }
            return $errors;
        }

        if (!is_array($field)) {
            $field = [$field => $errors];
        }

        foreach ($field as $f => $error) {
            $this->_errors[$f] = (array)$error;
        }

        return $this;
    }

    protected function _nestedErrors($field) {
        if (!isset($this->_properties[$field])) {
            return [];
        }

        $value = $this->_properties[$field];
        $errors = [];
        if (is_array($value) || $value instanceof \Traversable) {
            foreach ($value as $k => $v) {
                if (!($v instanceof self)) {
                    break;
                }
                $errors[$k] = $v->errors();
            }
            return $errors;
        }

        if ($value instanceof self) {
            return $value->errors();
        }

        return [];
    }

    public function accessible($property, $set = null) {
        if ($set === null) {
            return !empty($this->_accessible[$property]) || !empty($this->_accessible['*']);
        }

        if ($property === '*') {
            $this->_accessible = array_map(function ($p) use ($set) {
                return (bool)$set;
            }, $this->_accessible);
            $this->_accessible['*'] = (bool)$set;
            return $this;
        }

        foreach ((array)$property as $prop) {
            $this->_accessible[$prop] = (bool)$set;
        }

        return $this;
    }

    public function __toString() {
        $html = '<div class="' . $this->_className . '">';
        foreach ($this->_properties as $key => $val) {
            $html .= '<strong class="key">' . h($key) . '</strong>' . '<span clas="value">' . h(strval($val)) . '</span>';
        }
        $html .= '</div>';

        return $html;
    }

    public function alias() {
        return str_replace('Entity', '', $this->_className);
    }
}