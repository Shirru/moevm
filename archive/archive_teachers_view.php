<?php

function archive_teachers_view_page() {
    $teacher_id = $_GET['id'];
    $year = $_GET['year'];

    $output = "<h2>Данные за " . $year . " год</h2>";

    $output .= get_personal_teacher_data($teacher_id, $year);

    $output .= "<br><h3>Может вести</h3>";

    $output .= get_canteach_teacher_data($teacher_id, $year);

    $output .= "<br><h3>Аудиторная нагрузка</h3>";

    $output .= get_hallload_teacher_data($teacher_id, $year);

    $output .= "<br><h3>Неаудиторная нагрузка</h3>";

    $output .= get_extraload_teacher_data($teacher_id, $year);

    return $output;
}

function get_personal_teacher_data($teacher_id, $year) {
    $output = "";
    $header = array();
    $rows = array();

    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive_' . $year;


    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $teacher_result = $mysqli->query("SELECT *
							FROM `teacher` 
							WHERE `idTeacher` = '" . $teacher_id . "'");

    $ind_plan_result = $mysqli->query("SELECT *
                 FROM individualplan
                 WHERE `Teacher` = '" . $teacher_id . "'");

    $mysqli->close();

    if (!empty($ind_plan_result))
    {
        $ind_plan = $ind_plan_result->fetch_assoc();
        $ind_plan_result->close();
    }

    $teacher = $teacher_result->fetch_assoc();
    $teacher_result->close();

    $output .= "<h3>Личные данные</h3>";

    $rows[] = array("<b>Номер паспорта</b>", substr($teacher['Passport'], 0, 4) . " " . substr($teacher['Passport'], 4));
    $rows[] = array("<b>Фамилия</b>", $teacher['Surname']);
    $rows[] = array("<b>Имя</b>", $teacher['FirstName']);
    $rows[] = array("<b>Отчество</b>", $teacher['Patronymic']);
    $rows[] = array("<b>Должность</b>", $teacher['Position']);
    $rows[] = array("<b>Степень</b>", $teacher['Degree']);
    $rows[] = array("<b>Звание</b>", $teacher['Rank']);
    $rows[] = array("<b>Доля ставки</b>", $teacher['ShareRates']);
    $rows[] = array("<b>Состояние</b>", $teacher['Condition']);


    $output .= theme('table', array('header' => $header, 'rows' => $rows));

    $rows = array();

    $output .= "<br><h3>Контакты</h3>";

    $rows[] = array("<b>Мобильный телефон</b>", $teacher['Mobile']);
    $rows[] = array("<b>Рабочий телефон</b>", $teacher['WorkPhone']);
    $rows[] = array("<b>Домашний телефон</b>", $teacher['HomePhone']);
    $rows[] = array("<b>E-mail</b>", $teacher['E-mail']);
    $rows[] = array("<b>Адрес</b>", $teacher['Address']);

    $output .= theme('table', array('header' => $header, 'rows' => $rows));

    $rows = array();

    $output .= "<br><h3>Договор/контракт</h3>";

    $rows[] = array("<b>Вид договора</b>", $teacher['Contract']);
    $rows[] = array("<b>Эффективный контракт</b>", $teacher['EffectiveContract']);
    $rows[] = array("<b>Дата заключения договора</b>", $teacher['ConclusionDate']);
    $rows[] = array("<b>Дата окончания договора</b>", $teacher['TerminationDate']);

    $output .= theme('table', array('header' => $header, 'rows' => $rows));

    $output .= "<br><h3>Дополнительно</h3>";

    $rows = array();

    $rows[] = array("<b>Дата рождения</b>", $teacher['BirthDate']);

    if(isset($ind_plan)) {
        $rows[] = array("<b>Индивидуальный план (.doc)</b>", "<a href='" . file_create_url($ind_plan["WordFile"]) .
            "' download><img src = '/sites/all/pic/download.png'></a><a href=http://docs.google.com/viewer?url=" .
            file_create_url($ind_plan["WordFile"]) . "  title='просмотр'><img src = '/sites/all/pic/preview.png'></a>");
        $rows[] = array("<b>Индивидуальный план (.xls)</b>", "<a href='" . file_create_url($ind_plan["ExcelFile"]) .
            "' download><img src = '/sites/all/pic/download.png'></a><a href=http://docs.google.com/viewer?url=" .
            file_create_url($ind_plan["ExcelFile"]) . "  title='просмотр'><img src = '/sites/all/pic/preview.png'></a>");
    }

    $rows[] = array("<b>Заметки</b>", $teacher['Notes']);

    $output .= theme('table', array('header' => $header, 'rows' => $rows));

    return $output;
}

function get_canteach_teacher_data($teacher_id, $year) {
    $output = "";
    $header = array('Полное название', 'Краткое название');
    $rows = array();

    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive_' . $year;

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    // Дисциплины, которые может вести преподаватель
    $dis_result = $mysqli->query("SELECT `DisFullName`, `DisShortName`, `idDiscipline`
		FROM discipline 
		WHERE `idDiscipline` IN
		(SELECT `Discipline` FROM canteach
		WHERE `Teacher` = '" . $teacher_id . "')");

    $mysqli->close();

    if($dis_result) {
        foreach ($dis_result as $row) {
            $rows[] = array($row['DisFullName'], $row['DisShortName']);
        }

        $dis_result->close();
    }

    $table = theme('table', array('header' => $header, 'rows' => $rows));

    if(!empty($rows))
        return $table;
    else
        return 'Нет данных';
}

function get_hallload_teacher_data($teacher_id, $year) {
    $header = array('Дисциплина', 'Группа', 'Сем', 'Лек', 'Практ',
        'ЛР', 'КР', 'КП', 'Экз', 'ЗаО', 'За');
    $rows = array();

    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive_' . $year;

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $hall_load_result = mysqli_query ($mysqli, "SELECT * FROM `hallload`
		WHERE `Teacher` = '" . $teacher_id . "'");

    foreach ($hall_load_result as $row) {
        $discipline_result = mysqli_query($mysqli, "SELECT `DisFullName` FROM `discipline`
		WHERE `idDiscipline` = '" . $row['Discipline'] . "'");

        $discipline = $discipline_result->fetch_assoc();
        $discipline_result->close();

        $group_result = mysqli_query($mysqli, "SELECT `GroupNum` FROM `group`
		WHERE `idGroup` = '" . $row['Group'] . "'");

        $group = $group_result->fetch_assoc();
        $group_result->close();

        $rows[] = array(
            $discipline['DisFullName'],
            $group['GroupNum'],
            $row['Semestr'],
            $row['Lec'],
            $row['Pract'],
            $row['Lab'],
            $row['CourseWork'],
            $row['CourseProject'],
            $row['Exam'],
            $row['CreditWithGrade'],
            $row['CreditW/OGrade'],
        );
    }

    $hall_load_result->close();
    $mysqli->close();

    if(!empty($rows))
        return theme('table', array('header' => $header, 'rows' => $rows));
    else
        return "Нет данных";
}

function get_extraload_teacher_data($teacher_id, $year) {
    $header = array('Вид нагрузки', 'Норматив', 'Семестр', 'Кол-во часов');
    $rows = array();

    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive_' . $year;

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $extra_load_result = mysqli_query ($mysqli, "SELECT * FROM `extraload`
		WHERE `Teacher` = '" . $teacher_id . "'");

    foreach ($extra_load_result as $row) {
        $extra_load_kind_result = mysqli_query($mysqli, "SELECT `Name`, `Standart` FROM `extraloadkind`
		WHERE `idExtraLoadKind` = '" . $row['ExtraLoadKind'] . "'");

        $extra_load_kind = $extra_load_kind_result->fetch_assoc();
        $extra_load_kind_result->close();

        $rows[] = array(
            $extra_load_kind['Name'],
            $extra_load_kind['Standart'],
            $row['Semestr'],
            $row['Hour'],);
    }

    $extra_load_result->close();
    $mysqli->close();

    if(!empty($rows))
        return theme('table', array('header' => $header, 'rows' => $rows));
    else
        return "Нет данных";
}