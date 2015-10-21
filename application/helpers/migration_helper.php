<?php
if (!function_exists('module_exists')) {
    /**
     * Return the CodeIgniter modules list
     * @param bool $with_location
     * @return array
     */
    function modules_list($with_location = TRUE) {
        !function_exists('directory_map') && get_instance()->load->helper('directory');
        $modules = array();
        foreach (Modules::$locations as $location => $offset) {
            $files = directory_map($location, 1);
            if (is_array($files)) {
                foreach ($files as $name) {
                    if (is_dir($location . $name))
                        $modules[] = $with_location ? array($location, $name) : $name;
                }
            }
        }
        return $modules;
    }
    /**
     * Check if a CodeIgniter module with the given name exists
     * @param $module_name
     * @return bool
     */
    function module_exists($module_name)
    {
        return in_array($module_name, modules_list(FALSE));
    }
}
if (!function_exists('normalizePath')) {
	/**
	 * Remove the ".." from the middle of a path string
	 * @param string $path
	 * @return string
	 */
	function normalizePath($path)
	{
		$parts    = array(); // Array to build a new path from the good parts
		$path     = str_replace('\\', '/', $path); // Replace backslashes with forwardslashes
		$path     = preg_replace('/\/+/', '/', $path); // Combine multiple slashes into a single slash
		$segments = explode('/', $path); // Collect path segments
		foreach ($segments as $segment) {
			if ($segment != '.') {
				$test = array_pop($parts);
				if (is_null($test))
					$parts[] = $segment;
				else if ($segment == '..') {
					if ($test == '..')
						$parts[] = $test;
					if ($test == '..' || $test == '')
						$parts[] = $segment;
				} else {
					$parts[] = $test;
					$parts[] = $segment;
				}
			}
		}
		return implode('/', $parts);
	}
}
