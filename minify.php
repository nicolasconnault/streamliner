<?php
// Test change
require_once 'includes/js/Minifier.php';

function rscandir($base='', &$data=array()) {
    $array = array_diff(scandir($base), array('.', '..', '.svn')); # remove ' and .. from the array */
    foreach($array as $value) { /* loop through the array at the level of the supplied $base */
        if (is_dir($base.$value)) { /* if this is a directory */
            $data[] = $base.$value.'/'; /* add it to the $data array */
            $data = rscandir($base.$value.'/', $data); /* then make a recursive call with the
            current $value as the $base supplying the $data array to carry into the recursion */

        } else if (is_file($base.$value)) { /* else if the current $value is a file */
            $data[] = $base.$value; /* just add the current $value to the $data array */
        }
    }
    return $data; // return the $data array
}

function minify_dir($dir) {
    foreach ($dir as $file) {
        if (preg_match('/([^\/].*)\/(.*)\.js$/', $file, $matches)) {
            if (strstr($file, '.min.js')) {
                continue;
            }
            $filename = $matches[2];
            $directory = $matches[1];
            try {
                echo "minifying $directory/$filename.js...\n";
                file_put_contents("$directory/$filename.min.js", \JShrink\Minifier::minify(file_get_contents($file)));
            } catch (\Exception $e) {
                echo $file . " could not be minified, please check its syntax\n";
            }
        }
    }
}

// Browse through all JS files
$dir = rscandir('includes/js/');
minify_dir($dir);
$dir = rscandir('application/modules/');
minify_dir($dir);

// Minify every JS file, erasing existing versions
?>
