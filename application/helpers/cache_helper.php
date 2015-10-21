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
 * A shortcut function for cache. If the index is empty, usess the callback function for getting the value and saving to cache. Otherwise returns the cached value
 * @param string $index
 * @param function $callback
 * @param array $params Optional parameters for the callback function
 * @param object $object optional object, in which case the callback is a class method
 * @param boolean $enable If false, bypasses the caching. This makes it possible to switch caching on and off for individual queries
 * @param int $cache_time How long to cache the item, in seconds
 */
function get_or_save_cached($index, $callback, $params=array(), $object=null, $enable=true, $cache_time=60) {
    $ci = get_instance();
    $ci->load->driver('cache');

    if ($value = $ci->cache->apc->get($index) && $enable) {
        return $value;
    }

    if (empty($object)) {
        $value = call_user_func_array($callback, $params);
    } else {
        $value = call_user_func_array(array($object, $callback), $params);
    }

    if ($enable) {
        $ci->cache->apc->save($index, $value, $cache_time);
    }

    return $value;
}
