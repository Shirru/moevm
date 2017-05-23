<?php
/**
 * Created by PhpStorm.
 * User: annaf
 * Date: 23.05.2017
 * Time: 4:31
 */

function archive_groups_moevm_student_page() {
    $year = $_GET['year'];
    $student_id = $_GET['id'];

    $output = "<h2>Данные за " . $year . " год</h2><br>";

    $student = get_archive_student($student_id, $year);
    //dsm($student);

    $header = array();
    $rows = array();

    $rows[] = array("<b>Паспорт</b>", $student['Passport']);
    $rows[] = array("<b>Номер зачётной книжки</b>", $student['RecordBookNum']);
    $rows[] = array("<b>Фамилия</b>", $student['Surname']);
    $rows[] = array("<b>Имя</b>", $student['FirstName']);
    $rows[] = array("<b>Отчество</b>", $student['Patronymic']);
    $rows[] = array("<b>E-mail</b>", $student['E-mail']);
    $rows[] = array("<b>Номер группы</b>", $student['GroupNum']);
    $rows[] = array("<b>Адрес</b>", $student['Address']);
    $rows[] = array("<b>Телефон</b>", $student['Phone']);
    $rows[] = array("<b>Дата зачисления</b>", $student['EnrollmentDate']);
    if ("1900-01-01" != $student['ExpelDate'])
        $rows[] = array("<b>Дата отчисления</b>", $student['ExpelDate']);

    $output .= theme('table', array('header' => $header, 'rows' => $rows));

    return $output;
}

function get_archive_student($student_id, $year){
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive_' . $year;

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $student_result = mysqli_query ($mysqli, "SELECT * FROM `student`
    	WHERE `idStudent` = '" . $student_id . "'");

    $student = $student_result->fetch_assoc();
    $student_result->close();

    $group_result = mysqli_query ($mysqli, "SELECT `GroupNum` FROM `group`
    	WHERE `idGroup` = '" . $student['Group'] . "'");

    $group = $group_result->fetch_assoc();
    $group_result->close();
    $mysqli->close();

    $student['GroupNum'] = $group['GroupNum'];

    return $student;
}