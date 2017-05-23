<?php
/**
 * Created by PhpStorm.
 * User: annaf
 * Date: 23.05.2017
 * Time: 4:02
 */

function archive_groups_page() {
    $year = $_GET['year'];

    $output = "<h2>Данные за " . $year . " год</h2><br>";

    $output .= "<ul><li><a href = 'groups/moevm?year=" . $year . "'>Группы МОЭВМ</a></li>
                    <li><a href = 'groups/other?year=" . $year . "'>Группы других кафедр</a></li></ul>";

    return $output;
}