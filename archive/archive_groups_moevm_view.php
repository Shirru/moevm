<?php
/**
 * Created by PhpStorm.
 * User: annaf
 * Date: 23.05.2017
 * Time: 4:08
 */

function archive_groups_moevm_view_page() {
    $year = $_GET['year'];
    $group_id = $_GET['id'];

    $output = "<h2>Данные за " . $year . " год</h2><br>";

    $group = get_archive_group($group_id, $year);
    $students = get_archive_students($group_id, $year);

    $header = array();
    $rows = array();

    //dsm($group);
    //dsm($students);

    $rows[] = array("<b>Номер группы</b>", $group['GroupNum']);
    $rows[] = array("<b>Номер УП</b>", $group['CurriculumNum']);
    $rows[] = array("<b>Староста</b>", $group['Head']);
    $rows[] = array("<b>Год создания</b>", $group['CreationYear']);
    $rows[] = array("<b>Численность</b>", $group['Size']);
    $rows[] = array("<b>E-mail старосты/группы</b>", $group['E-mail']);

    $output .= theme('table', array('header' => $header, 'rows' => $rows));

    $output .= "<br><h3>Список студентов</h3>";

    $header = array('', '№', 'Номер зачетной книжки', 'Фамилия', 'Имя', 'Отчество');
    $rows = array();

    for ($i = 0; $i < sizeof($students); $i++) {
        $rows[] = array("<a href='student?id=" . $students[$i]["idStudent"] .
            "&year=" . $year . "'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
            $i+1, $students[$i]['RecordBookNum'],
            $students[$i]['Surname'], $students[$i]['FirstName'], $students[$i]['Patronymic']);
    }

    $output .= theme('table', array('header' => $header, 'rows' => $rows));

    return $output;
}

function get_archive_group($group_id, $year)
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

    $curriculum_result = mysqli_query ($mysqli, "SELECT `CurriculumNum`
    	FROM `curriculum`
    	WHERE `idCurriculum` = '" . $group['Curriculum'] . "'");

    $curriculum = $curriculum_result->fetch_assoc();
    $curriculum_result->close();

    $mysqli->close();

    $group['CurriculumNum'] = $curriculum['CurriculumNum'];

    return $group;
}

function get_archive_students($group_id, $year)
{
    $students = array();
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive_' . $year;

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $group_result = mysqli_query ($mysqli, "SELECT *
    	FROM `student`
    	WHERE `Group` = '" . $group_id . "'");

    foreach ($group_result as $student)
    {
        $students[] = $student;
    }

    $group_result->close();
    $mysqli->close();

    return $students;
}