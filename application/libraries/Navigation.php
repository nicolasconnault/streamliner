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
abstract class Navigation {

    public static function get_nav_number($title) {
        $nav_id = Navigation::get_nav_id(false);
        $number = ltrim($nav_id, 'nav_');
        $number++;

        return 'nav_' . $number;

    }

    public static function get_nav_id($increment=true) {
        static $nav_id = 0;
        if ($increment) {
            return $nav_id++;
        } else {
            return $nav_id;
        }
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
    public $links = array();

    public function __construct($title, $links, $caps=array()) {
        parent::__construct($title, $caps);

        $this->allowdoanything = false;

        foreach ($links as $link) {
            $this->links[] = new nav_link($link['title'],$link['link'],$link['caps'], @$link['icon'],  @$link['children']);
        }
    }

    public function render($topnav=false) {
        $retval = '';

        $nav_id = Navigation::get_nav_id(true);

        if ($this->matches_user_caps()) {

            if ($topnav) {
                $retval .= '
                  <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$this->title.' <b class="caret"></b></a>
                    <ul class="dropdown-menu" id="'.$nav_id.'">';
            } else {
                $retval .= '
                  <li class="nav-header">
                    <span class="" data-toggle="collapse" data-target="#'.$nav_id.'">'.$this->title.'</span>
                    </span>
                    <ul class="nav nav-list collapse in" id="'.$nav_id.'">';
            }

            $linksoutput = '';

            foreach ($this->links as $navlink) {
                $linksoutput .= $navlink->render($topnav);
            }

            if (empty($linksoutput)) {
                return '';
            }

            $retval .= $linksoutput . '</ul>'."\n";
        }
        return $retval;
    }
}

class nav_link extends nav_element {
    public $link;
    public $icon;
    public $children;

    public function __construct($title, $link, $caps=array(), $icon=null, $children=array()) {
        parent::__construct($title, $caps);
        $this->link = $link;
        $this->icon = $icon;
        $this->children = $children;
    }

    public function render($topnav=false) {
        $ci = get_instance();
        if ($this->matches_user_caps()) {
            $classes = '';

            if (base_url() . $ci->router->directory.$ci->router->class.'/'.$ci->router->method == $this->link) {
                $classes .= 'active';
            }
            $link = '<li><a class="'.$classes.'" href="' . base_url(). str_replace('&', '&amp;', $this->link) . '" title="' . $this->title . '">' .
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

            return $link;

        } else {
            return false;
        }
    }
}
