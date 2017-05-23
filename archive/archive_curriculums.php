<?php
/**
 * Created by PhpStorm.
 * User: annaf
 * Date: 22.05.2017
 * Time: 14:05
 */

function archive_curriculums_page() {

    $year = $_GET['year'];

    $output = "<h2>Данные за " . $year . " год</h2><br>";

    $output .= get_archive_bachelor_curriculums($year);
    $output .= get_archive_master_curriculums($year);

    return $output;
}

function get_archive_bachelor_curriculums($year) {
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive_' . $year;

    $output = "";
    $rows = array();

    $output .= "<h3>УП бакалавров</h3>";
    $header = array('', 'Номер', 'Код направления', 'Название направления', 'Кафедра');

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $bachelor_result = $mysqli->query("SELECT `idCurriculum`, `CurriculumNum`, 
                  `Direction`, `Chair`
                  FROM curriculum
                  WHERE (`Stage` = '1' AND `Chair` = 
                  (SELECT `idChair` FROM `chair`
                  WHERE `ChairNum` = 14))");

    foreach($bachelor_result as $row) {
        $direction_result = $mysqli->query("SELECT `DirectionCode`, `DirectionName`
                  FROM direction
                  WHERE `idDirection` = '" . $row["Direction"] . "'");
        $direction_row = $direction_result->fetch_row();
        $direction_code = $direction_row[0];
        $direction_name = $direction_row[1];
        $direction_result->close();

        $chair_result = $mysqli->query("SELECT `ChairShortName`
                 FROM chair
                 WHERE `idChair` = '" . $row["Chair"] . "'");

        $chair_row = $chair_result->fetch_row();
        $chair_id = $chair_row[0];
        $chair_result->close();

        $rows[] = array("<a href='/archive/curriculums/view?id=".$row ["idCurriculum"].
            "&year=" . $year . "'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
            $row["CurriculumNum"], $direction_code, $direction_name, $chair_id,);
    }

    $bachelor_result->close();
    $mysqli->close();

    $output .= theme('table', array('header' => $header, 'rows' => $rows));

    return $output;
}

function get_archive_master_curriculums($year) {
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive_' . $year;

    $output = "";
    $rows = array();
    $header = array('', 'Номер', 'Код направления', 'Название направления', 'Кафедра');

    $output .= "<h3>УП магистров</h3>";

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $master_result = $mysqli->query("SELECT `idCurriculum`, `CurriculumNum`, 
                  `Direction`, `Chair`
                  FROM curriculum
                  WHERE (`Stage` = '2' AND `Chair` = 
                  (SELECT `idChair` FROM `chair`
                  WHERE `ChairNum` = 14))");

    $rows_master = array();
    foreach($master_result as $row)
    {
        $direction_result = $mysqli->query("SELECT `DirectionCode`, `DirectionName`
                  FROM direction
                  WHERE `idDirection` = '" . $row["Direction"] . "'");
        $direction_row = $direction_result->fetch_row();
        $direction_code = $direction_row[0];
        $direction_name = $direction_row[1];
        $direction_result->close();

        $chair_result = $mysqli->query("SELECT `ChairShortName`
                 FROM chair
                 WHERE `idChair` = '" . $row["Chair"] . "'");
        $chair_row = $chair_result->fetch_row();
        $chair_id = $chair_row[0];
        $chair_result->close();


        $rows_master[] = array("<a href='/archive/curriculums/view?id=".$row ["idCurriculum"]."&year=" . $year . "'
          title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
            $row["CurriculumNum"], $direction_code, $direction_name, $chair_id,);
    }

    $master_result->close();
    $mysqli->close();

    $output .=  theme('table', array('header' => $header, 'rows' => $rows_master));

    return $output;
}