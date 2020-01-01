<?php

//Template Metadata
$tempMeta = [
    "name" => "Default Template",
    "desc" => "The Default Template for KCMS",
    "version" => "0.1",
    "owner_name" => "Kilian Kreibich",
    "owner_mail" => "mail@kilian-kreibich.de",
];

/**
 * Function that renders template
 * @param string $title  The Page Title
 * @param string $subtitle subtitle of the page
 * @param string $content Markup content of the page
 * @param int $image Media-ID of the image
 * @param bool $show_date Show the Date
 * @param bool $se_index Allow Search Engines to index
 */
function showTemp(string $title, string $subtitle, string $content, int $image, bool $show_date, bool $se_index){
    //TODO: Load template
}

/**
 * Function that renders special Index Template
 * @param string $title  The Page Title
 * @param string $subtitle subtitle of the page
 * @param string $content Markup content of the page
 * @param int $image Media-ID of the image
 * @param bool $show_date Show the Date
 * @param bool $se_index Allow Search Engines to index
 */
function showIndex(string $title, string $subtitle, string $content, int $image, bool $show_date, bool $se_index){
    //TODO: Load template
}
