<?php
/**
 * Created by PhpStorm.
 * User: annaf
 * Date: 23.05.2017
 * Time: 4:48
 */

function archive_groups_other_view_page() {
    $year = $_GET['year'];
    $group_id = $_GET['id'];

    $output = "<h2>Данные за " . $year . " год</h2><br>";

    $group = get_archive_other_group($group_id, $year);

    $header = array();
    $rows = array();

    $rows[] = array("<b>Номер группы</b>", $group['GroupNum']);
    $rows[] = array("<b>Численность</b>", $group['Size']);
    $rows[] = array("<b>Год создания</b>", $group['CreationYear']);
    $rows[] = array("<b>E-mail</b>", $group['E-mail']);
    $rows[] = array("<b>Номер УП</b>", $group['CurriculumNum']);
    $rows[] = array("<b>Код направления</b>", $group['DirectionCode']);
    $rows[] = array("<b>Название направления</b>", $group['DirectionName']);
    $rows[] = array("<b>Кафедра</b>", $group['ChairFullName']);
    $rows[] = array("<b>Номер кафедры</b>", $group['ChairNum']);

    //dsm($group);

    $output .= theme('table', array('header' => $header, 'rows' => $rows));

    return $output;
}

function get_archive_other_group($group_id, $year)
{
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive_' . $year;

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $group_result = mysqli_query ($mysqli, "SELECT *
    	FROM `group`
    	WHERE `idGroup` = '" . $group_id . "'");

    $group = $group_result->fetch_assoc();
    $group_result->close();

    $curriculum_result = mysqli_query ($mysqli, "SELECT `CurriculumNum`, `Chair`, `Direction` 
    	FROM `curriculum`
    	WHERE `idCurriculum` = '" . $group['Curriculum'] . "'");

    $curriculum = $curriculum_result->fetch_assoc();
    $curriculum_result->close();

    $chair_result =  mysqli_query ($mysqli, "SELECT `ChairNum`, `ChairFullName` FROM `chair`
		WHERE `idChair` = '" . $curriculum['Chair'] . "'");

    $chair = $chair_result->fetch_assoc();
    $chair_result->close();

    $direction_result =  mysqli_query ($mysqli, "SELECT `DirectionCode`, `DirectionName` FROM `direction`
		WHERE `idDirection` = '" . $curriculum['Direction'] . "'");

    $direction = $direction_result->fetch_assoc();
    $direction_result->close();

    $mysqli->close();

    $group['CurriculumNum'] = $curriculum['CurriculumNum'];
    $group['ChairFullName'] = $chair['ChairFullName'];
    $group['ChairNum'] = $chair['ChairNum'];
    $group['DirectionCode'] = $direction['DirectionCode'];
    $group['DirectionName'] = $direction['DirectionName'];

    return $group;
}