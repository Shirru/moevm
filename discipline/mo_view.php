<?php

function discipline_moevm_view_page()
{
    return drupal_get_form ( 'discipline_moevm_view_page_form' );
}

function discipline_moevm_view_page_form($form, &$form_state)
{
    $discipline_id = $_GET['dis'];
    global $user;
    $is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);

    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb';
    $teachers = array();
    $rows = array();
    $table = '';
    $teachers = array();
    array_push($teachers, "Выбрать преподавателя");

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $discipline_result = $mysqli->query("SELECT `idDiscipline`, `DisFullName`, `DisShortName` 
								 FROM discipline
								 WHERE `idDiscipline` = " . $discipline_id . "");

    $canteach_result = $mysqli->query("SELECT `idTeacher`, `surname`, `firstname`, `patronymic`
							from `teacher` 
							WHERE `idTeacher` IN 
							(SELECT `teacher` from `canteach` WHERE `Discipline` = " . $discipline_id . ")");

    $teachers_result = $mysqli->query("SELECT `Surname`, `FirstName`, `Patronymic`, `Initials`
								 FROM teacher");

    $mysqli->close();

    if(($is_teacher || $is_student) && !$is_educational)
        $header = array('', 'Фамилия', 'Имя', 'Отчество');
    else
        $header = array('', 'Фамилия', 'Имя', 'Отчество', '');

    foreach($canteach_result as $row)
    {
        if(($is_teacher || $is_student) && !$is_educational)
        {
            $rows[] = array("<a href='/teachers/view?id=".$row ["idTeacher"]."'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
                $row["surname"], $row["firstname"], $row["patronymic"]);
        }
        else
        {
            $rows[] = array("<a href='/teachers/view?id=".$row ["idTeacher"]."'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
                $row["surname"], $row["firstname"], $row["patronymic"],
                "<a href='#' onclick='if(confirm(\"Вы действительно хотите удалить запись `могут вести`?\")){parent.location = \"/disciplines/del?can_teach_teacher_id=" . $row ["idTeacher"] . "&dis_id=" . $discipline_id . "\";}else return false;'  title='удалить'><img src='/sites/all/pic/delete.png'></a>");
        }
    }

    if($canteach_result)
    {
        $canteach_result->close();
        $table .= theme('table', array('header' => $header, 'rows' => $rows));
    }


    $full_name = "";
    $short_name = "";

    foreach($discipline_result as $row)
    {
        $full_name .= $row["DisFullName"];
        $short_name .= $row["DisShortName"];
    }

    if($discipline_result)
        $discipline_result->close();

    foreach($teachers_result as $row)
    {
        $str = "";
        if($row["FirstName"] == "" && $row["Patronymic"] == "" && $row["Initials"] != "")
            $str .= $row["Surname"] . " " . $row["Initials"];
        else
            $str .= $row["Surname"] . " " . $row["FirstName"] . " " . $row["Patronymic"];
        array_push($teachers, $str);
    }

    $teachers_result->close();

    if(($is_teacher || $is_student) && !$is_educational)
    {
        $readonly = 'readonly';
    }
    else
    {
        $readonly = '';
    }

    $form = array();
    $form['full_name_dis'] = array(
        '#type' => 'textfield',
        '#title' => t('Полное название дисциплины'),
        '#default_value' => $full_name,
        '#attributes' => array(
            $readonly => array($readonly),),
        '#size' => 50,
    );

    $form['short_name_dis'] = array(
        '#type' => 'textfield',
        '#title' => t('Краткое название дисциплины'),
        '#default_value' => $short_name,
        '#attributes' => array(
            $readonly => array($readonly),),
        '#size' => 50,
    );

//	$table = "<h3>Могут вести</h3>" + $table;
//	debug($table);

    $form['can_teach_text'] = array(
        '#prefix' => "<br><h3>Могут вести</h3>",
        '#markup' => $table,
    );

    if(!(($is_teacher || $is_student) && !$is_educational))
    {
        $form['add_teacher_text'] = array(
            '#markup' => '<h3>Добавить преподавателя</h3>',
        );

        $form['can_teach_block'] = array(
            '#prefix' => '<div id = "can-teach-div">',
            '#suffix' => '</div>',
            '#type' => 'fieldset',
        );

        if(isset($form_state['values']))
        {
            $canteach_count = $form_state['storage']['count'];
            if($form_state['values']['teachers_select' . $canteach_count] != 0)
            {
                $form_state['storage']['count'] ++;
            }

            $canteach_count = $form_state['storage']['count'];
        }
        else
        {
            $canteach_count = 1;
            $form_state['storage']['count'] = 1;
        }

        for ($i = 1; $i <= $canteach_count; $i++)
        {
            $form['can_teach_block']['teachers_select' . $i] = array(
                '#type' => 'select',
                '#options' => $teachers,
                '#default_value' => 0,
                '#ajax' => array(
                    // Функция, которая сработает при выборе значения в списке,
                    // и которая должна вернуть новую часть формы
                    'callback' => 'discipline_moevm_view_page_form_ajax_callback',
                    // Id html элемента, в который будет выведена часть формы
                    'wrapper' => 'can-teach-div',
                ),
            );
        }

        $form['submit'] = array(
            '#type' => 'submit',
            '#value' => t('Сохранить'),
        );
    }

    return $form;
}

function discipline_moevm_view_page_form_ajax_callback($form, $form_state) {
    return $form['can_teach_block'];
}

function discipline_moevm_view_page_form_submit($form, &$form_state) {
    $discipline_id = $_GET['dis'];
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb';
    $chair = "МО ЭВМ";
    $canteach_count = isset($form_state['storage']) ? $form_state['storage']['count'] : 1;
    $chosen_teachers = array();

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $full_name = $form_state['values']['full_name_dis'];
    $short_name = isset($form_state['values']['short_name_dis']) ? $form_state['values']['short_name_dis'] : "";

    $is_success = mysqli_query($mysqli,"UPDATE `discipline`
							SET `DisFullName` = '" . $full_name . "',
							`DisShortName` = '" . $short_name . "' 
							WHERE `idDiscipline` = '" . $discipline_id . "'");

    if($is_success)
        drupal_set_message("Данные обновлены успешно");
    else
        drupal_set_message("Произошла ошибка при обновлении данных");

    $name = array();
    for($i = 1; $i <= $canteach_count; $i++)
    {
        if($form_state['values']['teachers_select' . $i] != 0)
        {
            array_push($chosen_teachers, $form['can_teach_block']['teachers_select' . $i]['#options'][$form_state['values']['teachers_select' . $i]]);
            $name = explode(' ', $chosen_teachers[$i - 1]);

            if($name[1] != "" && !isset($name[2]))
            {
                mysqli_query($mysqli,"INSERT INTO `canteach`
				(`Discipline`, `Teacher`)
				SELECT '" . $discipline_id . "', `idTeacher`
				FROM `teacher`
				WHERE (`Surname` = '" . $name[0] . "'
				AND `Initials` = '" . $name[1] . "')");
            }
            else if($name[1] == "" && $name[2] == "")
            {
                mysqli_query($mysqli,"INSERT INTO `canteach`
				(`Discipline`, `Teacher`)
				SELECT '" . $discipline_id . "', `idTeacher`
				FROM `teacher`
				WHERE (`Surname` = '" . $name[0] . "'
				AND `FirstName` IS NULL
				AND `Patronymic` IS NULL )");
            }
            else
            {
                mysqli_query($mysqli,"INSERT INTO `canteach`
				(`Discipline`, `Teacher`)
				SELECT '" . $discipline_id . "', `idTeacher`
				FROM `teacher`
				WHERE (`Surname` = '" . $name[0] . "' AND
				`FirstName` = '" . $name[1] . "' AND
				`Patronymic` = '" . $name[2] . "')");
            }

        }
    }


    mysqli_close($mysqli);

    //	drupal_goto("/moevm/edit");
}

