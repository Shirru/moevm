<?php
/**
 * Created by PhpStorm.
 * User: annaf
 * Date: 22.05.2017
 * Time: 14:59
 */

function archive_curriculums_view_discipline_page() {
    $dis_id = $_GET['id'];
    $year = $_GET['year'];

    $output = "<h2>Данные за " . $year . " год</h2><br>";

    $discipline = get_archive_curriculum_discipline($dis_id, $year);

    //dsm($discipline);
    $header = array();
    $rows = array();

    $rows[] = array("<b>Индекс</b>", $discipline['DisIndex']);
    $rows[] = array("<b>Название</b>", $discipline['DisciplineName']);
    $rows[] = array("<b>Семестр</b>", $discipline['Semester']);
    $rows[] = array("<b>Номер УП</b>", $discipline['CurriculumNum']);
    $rows[] = array("<b>Кафедра</b>", $discipline['ChairFullName']);

    $uri = get_archive_work_program_path($dis_id, $year);

    if(!empty($uri))
    {
        $file_name = drupal_basename($uri);
        $url = file_create_url($uri);
        $rows[] = array("<b>Рабочая программа</b>", $file_name . '<a href = "http://docs.google.com/viewer?url='
            . $url . '" target="_blank"><img src = "/sites/all/pic/preview.png" title = "Просмотр"></a><a href="'
                . $url . '"  title="скачать"><img src="/sites/all/pic/download.png"></a>');
    }

    $output .= theme('table', array('header' => $header, 'rows' => $rows));

    $header = array('Экз', 'За', 'ЗаО', 'Лек', 'Лаб', 'Практ', 'СРС', 'КП', 'КР', 'ЗЕТ', 'Всего');
    $rows = array();

    $rows[] = array($discipline['Exam'], $discipline['CreditW/OGrade'], $discipline['CreditWithGrade'], $discipline['Lecture'],
        $discipline['Lab'], $discipline['Practice'], $discipline['Solo'], $discipline['CourseProject'],
        $discipline['CourseWork'], $discipline['Zet'], $discipline['Total']);

    $output .= "<br>" . theme('table', array('header' => $header, 'rows' => $rows));
    return $output;
}

function get_archive_curriculum_discipline($dis_id, $year) {
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive_' . $year;

    $curriculum_discipline = array();

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $curriculum_discipline_result = mysqli_query ($mysqli, "SELECT * FROM `curriculumdiscipline`
		WHERE `idCurriculumDiscipline` = '" . $dis_id . "'");

    $curriculum_discipline = $curriculum_discipline_result->fetch_assoc();
    $curriculum_discipline_result->close();

    $discipline_result = mysqli_query ($mysqli, "SELECT `DisFullName`, `Chair` FROM `discipline`
		WHERE `idDiscipline` = '" . $curriculum_discipline['Discipline'] . "'");

    $discipline = $discipline_result->fetch_assoc();
    $discipline_result->close();

    $curriculum_result = mysqli_query ($mysqli, "SELECT `CurriculumNum` FROM `curriculum`
		WHERE `idCurriculum` = '" . $curriculum_discipline['Curriculum'] . "'");

    $curriculum = $curriculum_result->fetch_assoc();
    $curriculum_result->close();

    $chair_result = mysqli_query ($mysqli, "SELECT `ChairFullName` FROM `chair`
		WHERE `idChair` = '" . $discipline['Chair'] . "'");

    $chair = $chair_result->fetch_assoc();
    $chair_result->close();
    $mysqli->close();

    $curriculum_discipline['DisciplineName'] = $discipline['DisFullName'];
    $curriculum_discipline['CurriculumNum'] = $curriculum['CurriculumNum'];
    $curriculum_discipline['ChairFullName'] = $chair['ChairFullName'];

    return $curriculum_discipline;
}

function get_archive_work_program_path($dis_id, $year) {
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive_' . $year;

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $work_program_result = mysqli_query ($mysqli, "SELECT `FileName` FROM `workprogramversion`
		WHERE (`CurriculumDiscipline` = '" . $dis_id . "'
		AND `CurrentVersion` = 1)");

    foreach ($work_program_result as $row)
    {
        $path = $row['FileName'];
    }

    if(isset($path))
        return $path;
    else
        return '';
}