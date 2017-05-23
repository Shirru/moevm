<?php
/**
 * Created by PhpStorm.
 * User: annaf
 * Date: 22.05.2017
 * Time: 14:25
 */

function archive_curriculums_view_page() {
    $year = $_GET['year'];
    $curriculum_id = $_GET['id'];

    $output = "<h2>Данные за " . $year . " год</h2><br>";

    $output .= get_archive_curriculum_data($year, $curriculum_id);

    return $output;
}

function get_archive_curriculum_data($year, $curriculum_id) {
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive_' . $year;

    $curriculum_num = "";

    $header = array('', 'Индекс', 'Дисциплина', '<abbr title="Экзамен">Экз</abbr>', '<abbr title="Зачет">За</abbr>',
        '<abbr title="Зачет с оценкой">ЗаО</abbr>', '<abbr title="Лекции">Лек</abbr>', '<abbr title="Лабораторные работы">Лаб</abbr>',
        '<abbr title="Практические занятия">Практ</abbr>', '<abbr title="Самостоятельная работа студентов">СРС</abbr>',
        '<abbr title="Курсовой проект">КП</abbr>',
        '<abbr title="Курсовая работа">КР</abbr>', '<abbr title="Зачетные единицы времени в часах (единица ЗЕТ равна 36
            академическим часам)">ЗЕТ</abbr>', 'Всего',
        '<abbr title="Семестр">Сем</abbr>');

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $curriculum_result = $mysqli->query("SELECT `CurriculumNum`, 
                  `Direction`, `Chair`
                 FROM curriculum
                 WHERE `idCurriculum` = '" . $curriculum_id . "'");

    foreach($curriculum_result as $row)
    {
        $curriculum_num = $row["CurriculumNum"];
        $direction = $row["Direction"];
    }
    $curriculum_result->close();

    $cur_discipline_result = $mysqli->query("SELECT *
                 FROM curriculumdiscipline 
                 WHERE (`curriculum` = '" . $curriculum_id . "') ORDER BY `Semester`");

    $rows = array();

    foreach($cur_discipline_result as $row)
    {
        $discipline_result = $mysqli->query("SELECT `DisFullName`
                 FROM discipline
                 WHERE `idDiscipline` ='" . $row["Discipline"]  . "' ORDER BY `DisFullName`");
        $discipline_name = $discipline_result->fetch_row();
        $discipline_result->close();

        $rows[] = array("<a href='discipline?id=".$row ["idCurriculumDiscipline"]."&year=" . $year . "' 
         title='просмотр'><img src='/sites/all/pic/view.png'></a>",
            $row["DisIndex"], $discipline_name[0], $row["Exam"],
            $row["CreditW/OGrade"], $row["CreditWithGrade"],
            $row["Lecture"], $row["Lab"], $row["Practice"],
            $row['Solo'],
            $row["CourseProject"], $row["CourseWork"],
            $row['Zet'], $row['Total'],
            $row["Semester"],
        );


    }
    $cur_discipline_result->close();

    $direction_result = $mysqli->query("SELECT `idDirection`, `DirectionCode`,`DirectionName`
                 FROM `direction`
                 WHERE `idDirection` = '" . $direction . "'");

    $direction_code = "";
    $direction_name = "";

    foreach ($direction_result as $row)
    {
        $direction_name = $row['DirectionName'];
        $direction_code = $row['DirectionCode'];
        $direction_id = $row['idDirection'];
    }
    $direction_result->close();

    $mysqli->close();
    $table = theme('table', array('header' => $header, 'rows' => $rows));

    $header = array();
    $rows = array();

    $rows[] = array("<b>Номер УП</b>", $curriculum_num);
    $rows[] = array("<b>Код направления</b>", $direction_code);
    $rows[] = array("<b>Название направления</b>", $direction_name);

    $output = theme('table', array('header' => $header, 'rows' => $rows));

    $output .= "<br><h3>Список дисциплин</h3>";
    $output .= $table;

    return $output;
}