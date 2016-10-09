<?php
$classes = array_diff(scandir('models'), array('..', '.'));

if (!empty($classes) && $classes!= false){
    foreach ($classes as $class){
        require_once('models/'. $class);
    }
}
