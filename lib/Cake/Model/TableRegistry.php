<?php
App::uses('ConnectionManager', 'Model');
App::uses('Inflector', 'Utility');
App::uses('Table', 'Model');

class TableRegistry {
    protected static $_config = [];
    protected static $_instances = [];

    public static function config($alias = null, $options = null) {
        if ($alias === null) {
            return static::$_config;
        }
        if (!is_string($alias)) {
            return static::$_config = $alias;
        }
        if ($options === null) {
            return isset(static::$_config[$alias]) ? static::$_config[$alias] : [];
        }
        if (isset(static::$_instances[$alias])) {
            throw new RuntimeException(sprintf(
                'You cannot configure "%s", it has already been constructed.',
                $alias
            ));
        }
        return static::$_config[$alias] = $options;
    }

    public static function get($alias, $options = []) {
        $exists = isset(static::$_instances[$alias]);
        if ($exists && !empty($options)) {
            throw new RuntimeException( sprintf( 'You cannot configure "%s", it already exists in the registry.', $alias ) );
        }
        if ($exists) {
            return static::$_instances[$alias];
        }

        list($plugin, $baseClass) = pluginSplit($alias);
        $options = ['alias' => $baseClass] + $options;

        if (empty($options['className'])) {
            $class = Inflector::camelize($alias);
            if (!class_exists($class . 'Table')) {
                App::uses($class . 'Table', 'Model/Table');
            }
            if (class_exists($class . 'Table')) {
                $options['className'] = $options['class'] = $class . 'Table';
            } else {
                $options['className'] = $options['class'] = 'Table';
            }
        }

        if (isset(static::$_config[$alias])) {
            $options = array_merge(static::$_config[$alias], $options);
        }
        if (empty($options['connection'])) {
            $connectionName = $options['className']::defaultConnectionName();
            $options['ds'] = $options['connection'] = ConnectionManager::getDataSource($connectionName);
        }
        return static::$_instances[$alias] = ClassRegistry::init($options['className'], $options);
    }

    public static function exists($alias) {
        return isset(static::$_instances[$alias]);
    }

    public static function set($alias, Table $object) {
        return static::$_instances[$alias] = $object;
    }

    public static function clear() {
        static::$_instances = [];
        static::$_config = [];
    }
}