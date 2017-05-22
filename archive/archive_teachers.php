<?php
/**
 * Created by PhpStorm.
 * User: annaf
 * Date: 21.05.2017
 * Time: 21:16
 */

function archive_teachers_page(){
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive';

    $year = $_GET['year'];

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $header = array('', 'Фамилия', 'Имя', 'Отчество', 'Должность', 'Степень', 'Звание', 'Состояние');
    $rows = array();
    $output = "<h2>Данные за " . $year . " год</h2>";

    $teachers_result = $mysqli->query("SELECT *
                 FROM teacher  WHERE `Year` = " . $year . " ORDER BY `Surname`, `FirstName`, `Patronymic`");

    $mysqli->close();

    foreach($teachers_result as $row)
    {

        $rows[] = array("<a href='teachers/view?id=" . $row ["idTeacher"] . "&year=" . $year . "'  title='просмотр'>
                <img src='/sites/all/pic/edit.png'></a>",
            $row["Surname"], $row["FirstName"], $row["Patronymic"],
            $row["Position"], $row["Degree"], $row["Rank"],
            $row["Condition"],
        );

    }

    $teachers_result->close();
    $output .= theme('table', array('header' => $header, 'rows' => $rows));

    return $output;
}