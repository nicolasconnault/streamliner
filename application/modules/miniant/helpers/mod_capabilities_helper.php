<?php
function is_tech() {
    return has_capability('orders:viewassignedorders') && !has_capability('orders:viewclientinfo');
}

function is_admin() {
    return has_capability('site:donaything');
}

function is_accounts() {
    return has_capability('orders:changestatustosent') && has_capability('site:createrepair_jobnumbers') && !has_capability('site:doanything');
}

function is_sm() {
    return has_capability('orders:changestatustosent') && has_capability('site:createrepair_jobnumbers') && !has_capability('site:doanything');
}
?>
