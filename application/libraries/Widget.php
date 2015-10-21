<?php
abstract class Widget {
    public $id;
    public $label;
    public $width = 6; // In bootstrap columns (1-12)
    public $row = 1; // Bootstrap row (1-3)

    abstract public function get_html();

}
