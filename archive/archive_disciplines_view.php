<?php
/**
 * Created by PhpStorm.
 * User: annaf
 * Date: 22.05.2017
 * Time: 3:09
 */

function archive_disciplines_view_page() {
    $output = "";

    $discipline_id = $_GET['dis'];
    $year = $_GET['year'];

    $output = "<h2>Данные за " . $year . " год</h2>";

    $output .= get_archive_discipline_data($discipline_id, $year);

    $output .= "<br><h3>Могут вести</h3>";

    $output .= get_archive_canteach_data($discipline_id, $year);


    return $output;
}

function get_archive_discipline_data($discipline_id, $year) {
    $output = "";
    $header = array('Полное название дисциплины', 'Краткое название дисциплины');
    $rows = array();

    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive_' . $year;


    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $discipline_result = $mysqli->query("SELECT *
							FROM `discipline` 
							WHERE `idDiscipline` = '" . $discipline_id . "'");

    $work_program_result = $mysqli->query("SELECT a.FileName, cur.CurriculumNum, a.CurrentVersion
                    FROM 
                        (SELECT wp.FileName, wp.CurriculumDiscipline, wp.CurrentVersion, cd.Curriculum, cd.Discipline
                         FROM workprogramversion wp
                         LEFT OUTER JOIN curriculumdiscipline cd ON wp.CurriculumDiscipline = cd.idCurriculumDiscipline) a
                    LEFT OUTER JOIN curriculum cur ON a.Curriculum = cur.idCurriculum
                    WHERE a.Discipline = " . $discipline_id . " && a.CurrentVersion = 1");

    $mysqli->close();

    if (isset($discipline_result)) {
        $discipline = $discipline_result -> fetch_assoc();
        $discipline_result -> close();

        $rows[] = array($discipline['DisFullName'], $discipline['DisShortName']);
    }

    $output .= theme('table', array('header' => $header, 'rows' => $rows));

    $rows = array();

    $output .= "<br><h3>Рабочие программы дисциплины</h3>";
    $header = array('Номер учебного плана', 'Рабочая программа');

    if($work_program_result) {
        foreach ($work_program_result as $row) {
            $rows[] = array($row["CurriculumNum"],
                basename($row["FileName"]) . " <a href='"
                . file_create_url($row["FileName"]) . "'  title='скачать'><img src='/sites/all/pic/download.png'></a>
                <a href=http://docs.google.com/viewer?url=" . file_create_url($row["FileName"]) . "  title='просмотр'>
                <img src='/sites/all/pic/preview.png'></a> "
            );
        }

        $work_program_result -> close();
    }

    $output .= theme('table', array('header' => $header, 'rows' => $rows));

    return $output;
}

function get_archive_canteach_data($discipline_id, $year) {
    $output = "";
    $header = array('', 'Фамилия', 'Имя', 'Отчество');
    $rows = array();

    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive_' . $year;


    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $canteach_result = $mysqli->query("SELECT `idTeacher`, `surname`, `firstname`, `patronymic`
							from `teacher` 
							WHERE `idTeacher` IN 
							(SELECT `teacher` from `canteach` WHERE `Discipline` = " . $discipline_id . ")");
    $mysqli->close();

    if($canteach_result)
    {
        foreach($canteach_result as $row) {
            $rows[] = array("<a href='/archive/teachers/view?id=".$row ["idTeacher"]."&year=" . $year . "'  title='просмотр'>
            <img src='/sites/all/pic/view.png'></a>",
                $row["surname"], $row["firstname"], $row["patronymic"]);
        }
        $canteach_result->close();
        $output .= theme('table', array('header' => $header, 'rows' => $rows));
    }

    return $output;
}