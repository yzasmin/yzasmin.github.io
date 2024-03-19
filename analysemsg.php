<?php

function loadScoreMap() {
    $json = file_get_contents('score_map.json');
    return json_decode($json, true);
}

function evaluateMessage($message, $scoreMap) {
    $score = 0;
    $words = explode(" ", $message);
    foreach ($words as $word) {
        if (array_key_exists($word, $scoreMap)) {
            $score += $scoreMap[$word];
        }
    }
    return $score;
}