<?php
function get_widgets() {
    $modules = scandir(APPPATH.'modules');
    $widgets = array();

    foreach ($modules as $module) {
        if (in_array($module, array('.', '..'))) {
            continue;
        }

        $has_widgets_folder = file_exists(APPPATH.'modules/'.$module.'/widgets');

        $widget_folder = APPPATH.'modules/'.$module.'/widgets';
        if (!file_exists($widget_folder)) {
            continue;
        }

        $module_widgets = scandir($widget_folder);

        foreach ($module_widgets as $module_widget) {
            if (in_array($module_widget, array('.', '..'))) {
                continue;
            }

            if (!preg_match('/^([a-z\-\_]*)\.php/', $module_widget, $widget_parts)) {
                continue;
            }

            $widget_name = $widget_parts[1];

            require_once("$widget_folder/$module_widget");

            $widget_class ="Widget_$module"."_$widget_name";
            if (class_exists($widget_class)) {
                $widgets[] = new $widget_class();
            }
        }
    }

    return $widgets;
}
