<?php

function get_hours_and_minutes_from_seconds($seconds) {

    $hours = floor($seconds / 60 / 60);

    $minutes = ceil((($seconds / 60) - ($hours * 60)) / 15) * 15; // Round up to the next 15 minutes

    if ($minutes == 60) {
        $minutes = 0;
        $interval->h++;
    }

    return sprintf("%'.02d", $hours) . ':'.sprintf("%'.02d", $minutes);
}
