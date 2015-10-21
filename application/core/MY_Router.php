<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Copyright 2015 SMB Streamline
 *
 * Licensed under the "Attribution-NonCommercial-ShareAlike" Vizsage
 * Public License (the "License"). You may not use this file except
 * in compliance with the License. Roughly speaking, non-commercial
 * users may share and modify this code, but must give credit and
 * share improvements. However, for proper details please
 * read the full License, available at
 *  	http://vizsage.com/license/Vizsage-License-BY-NC-SA.html
 * and the handy reference for understanding the full license at
 *  	http://vizsage.com/license/Vizsage-Deed-BY-NC-SA.html
 *
 * Unless required by applicable law or agreed to in writing, any
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 */
/**
 * MY_Router Class
 *
 * @author Damien K.
 */
/* load the MX_Router class */
require APPPATH."third_party/MX/Router.php";
class MY_Router extends MX_Router {}
{
    // --------------------------------------------------------------------

    /**
     * OVERRIDE
     */
    function _validate_request($segments)
    {
        // Does the requested controller exist in the root folder?
        if (file_exists(APPPATH.'controllers/'.$segments[0].'.php'))
        {
            return $segments;
        }

        // Is the controller in a sub-folder?
        if (is_dir(APPPATH.'controllers/'.$segments[0]))
        {
            // EDIT:
            $dir = '';
            do
            {
                if (strlen($dir) > 0)
                {
                    $dir .= '/';
                }
                $dir .= $segments[0];
                $segments = array_slice($segments, 1);
            } while (is_dir(APPPATH.'controllers/'.$dir .'/'.$segments[0]));
            // Set the directory and remove it from the segment array
            $this->set_directory($dir);
            // END EDIT:

            if (count($segments) > 0)
            {
                // Does the requested controller exist in the sub-folder?
                if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$segments[0].'.php'))
                {
                    show_404($this->fetch_directory().$segments[0]);
                }
            }
            else
            {
                $this->set_class($this->default_controller);
                $this->set_method('index');

                // Does the default controller exist in the sub-folder?
                if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$this->default_controller.'.php'))
                {
                    $this->directory = '';
                    return array();
                }

            }

            return $segments;
        }

        // Can't find the requested controller...
        show_404($segments[0]);
    }

    function set_directory($dir)
	{
		$this->directory = $dir.'/';
	}

    function set_class($class)
	{
		$this->class = $class;
	}
}

// END MY_Router class

/* End of file MY_Router.php */
/* Location: ./system/application/libraries/MY_Router.php */
