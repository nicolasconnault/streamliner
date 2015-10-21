<?php
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
 * Method to show a title bar for a navigation menu box.
 *
 * @access public
 * @static
 * @param string $title the title of the box
 *
 * @return string HTML output
 *
 */
function get_nav_number($title) {
    $nav_id = get_nav_id(false);
    $number = ltrim($nav_id, 'nav_');
    $number++;

    return 'nav_' . $number;

}

function get_nav_id($increment=true) {
    static $nav_id = 0;
    if ($increment) {
        return $nav_id++;
    } else {
        return $nav_id;
    }
}

abstract class nav_element {
    public $title;
    /**
     * @var boolean $allowdoanything Whether or not admins can ignore the caps. True means that they can.
     */
    public $allowdoanything = true;
    public $caps = array();

    public function __construct($title, $caps=array()) {
        $this->caps = $caps;
        $this->title = $title;
    }

    public function matches_user_caps() {
        foreach ($this->caps as $capname) {
            if (!has_capability($capname, null, $this->allowdoanything)) {
                return false;
            }
        }
        return true;
    }

    abstract function render($topnav=false);
}

class nav_menu extends nav_element {
    public $links = array(); // $links can also be a single link, in which case there is no dropdown

    public function __construct($title, $links, $caps=array()) {
        parent::__construct($title, $caps);

        $this->allowdoanything = false;
        if (!isset($links['title'])) {
            foreach ($links as $link) {
                $this->links[] = new nav_link($link['title'],$link['link'],$link['caps'], @$link['icon'],  @$link['children']);
            }
        } else {
            $this->links = new nav_link($links['title'],$links['link'],$links['caps'], @$links['icon'],  @$links['children'], true);
        }
    }

    public function render($topnav=false) {
        $retval = '';

        $nav_id = get_nav_id(true);

        if ($this->matches_user_caps()) {

            $linksoutput = '';

            if (is_array($this->links)) {
                if ($topnav) {
                    $retval .= '
                      <li>
                        <a href="#">'.$this->title.'
                        <span class="fa arrow"></span>
                        </a>
                        <ul class="nav nav-second-level" id="'.$nav_id.'">';
                } else {
                    $retval .= '
                      <li>
                        <span class="" >'.$this->title.'</span>
                        </span>
                        <ul class="nav nav-second-level" id="'.$nav_id.'">';
                }

                foreach ($this->links as $navlink) {
                    $linksoutput .= $navlink->render($topnav);
                }
                $retval .= $linksoutput . '</ul>'."\n";
            } else {
                $linksoutput .= $this->links->render($topnav);
                $retval .= $linksoutput;
            }

            if (empty($linksoutput)) {
                return '';
            }
        }
        return $retval;
    }
}

class nav_link extends nav_element {
    public $link;
    public $icon;
    public $children;
    public $single_link=false;

    public function __construct($title, $link, $caps=array(), $icon=null, $children=array(), $single_link=false) {
        parent::__construct($title, $caps);
        $this->link = $link;
        $this->icon = $icon;
        $this->children = $children;
        $this->single_link = $single_link;
    }

    public function render($topnav=false) {
        $ci = get_instance();
        if ($this->matches_user_caps()) {
            $classes = '';

            if (base_url() . $ci->router->directory.$ci->router->class.'/'.$ci->router->method == $this->link) {
                $classes .= 'active';
            }

            $id = 'topnav-link-'.strtolower(str_replace(' ', '-', $this->title));

            if ($this->single_link) {
                $link = '<li id="'.$id.'"><a class="'.$classes.'" href="' . base_url(). str_replace('&', '&amp;', $this->link) . '" title="' . $this->title . '">' .
                    '<i class="fa fa-fw fa-'.$this->icon.'"></i> '. $this->title . '</a></li>'."\n";

            } else {
                $link = '<li id="'.$id.'"><a class="'.$classes.'" href="' . base_url(). str_replace('&', '&amp;', $this->link) . '" title="' . $this->title . '">' .
                    '<i class="fa fa-fw fa-'.$this->icon.'"></i> '. $this->title . '</a>'."\n";

                if (!$topnav) {
                    if (!empty($this->children)) {
                        $link .= '<ul class="nav nav-list nav-list-sub">';
                        foreach ($this->children as $child_link) {
                            $link .= '<li';

                            if (base_url() .
                                $ci->router->directory.
                                $ci->router->class.'/'.
                                $ci->router->method ==
                                $child_link['link']) {
                                $link .= ' class="active" ';
                            }
                            $link .= '>';
                            $link .= '<a href="'.base_url().$child_link['link'].'" target="_self"><i class="icon-fixed-width fa-'.$child_link['icon'].'"></i>'.$child_link['title'].'</a></li>';

                        }
                        $link .= '</ul>';
                    }
                }

                $link .= '</li>';
            }

            return $link;

        } else {
            return false;
        }
    }
}

function get_dynamic_nav($topnav=false) {
    $nav = '';
    $ci = get_instance();
    $top_nav = $ci->config->item('top_nav');

    if (is_array($top_nav)) {
        foreach ($top_nav as $top_menu => $links) {
            $navmenu = new nav_menu($top_menu, $links);
            $nav .= $navmenu->render($topnav);
        }
    }
    return $nav;
}

?>
