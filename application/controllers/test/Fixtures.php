<?php

class unit_test_fixtures {
    public $_ci;
    public $loaded_tables=array();

    public function __construct($ci) {
        $this->_ci = $ci;
    }

    public function reset_table($table) {
        $this->_ci->db->truncate($table);
        if (false !== ($key = array_search($table, $this->loaded_tables))) {
            unset($this->loaded_tables[$key]);
        }
    }

    public function reset_tables($tables=array()) {
        foreach ($tables as $table) {
            $this->reset_table($table);
        }
    }

    public function __call($function_name, $arguments=array()) {
        if (preg_match('/load_([a-z_]*)/', $function_name, $matches) && method_exists($this, "_".$function_name)) {
            $table = $matches[1];
            $this->_ci->db->truncate($table);
            $this->{'_'.$function_name}();
        } else {
            echo "The function $function_name() is not yet supported, edit the tests/models/fixtures.php file to write it.";
            return false;
        }
    }

    public function load_all() {
        $methods = get_class_methods('unit_test_fixtures');
        foreach ($methods as $method) {
            if (preg_match('/^_load_([a-zA-Z0-9\-\_]*)/i', $method, $matches) && $method != 'load_all') {
                $this->reset_table($matches[1]);
                $this->{$method}();
            }
        }
    }

}
