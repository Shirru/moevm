<?php

function discipline_moevm_add_page()
{
    return drupal_get_form ( 'discipline_moevm_add_page_form' );
}

function discipline_moevm_add_page_form($form, &$form_state)
{
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb';

    $teachers = array();
    array_push($teachers, "Выбрать преподавателя");

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $result = $mysqli->query("SELECT `Surname`, `FirstName`, `Patronymic`
								 FROM teacher");
    $mysqli->close();

    foreach($result as $row)
    {
        $str = "";
        $str .= $row["Surname"] . " " . $row["FirstName"] . " " . $row["Patronymic"];
        array_push($teachers, $str);
    }
    $result->close();

    $form = array();
    $form['full_name_dis'] = array(
        '#type' => 'textfield',
        '#title' => t('Полное название дисциплины'),
        '#size' => 50,
    );

    $form['short_name_dis'] = array(
        '#type' => 'textfield',
        '#title' => t('Краткое название дисциплины'),
        '#size' => 50,
    );

    $form['can_teach_text'] = array(
        '#markup' => "<h3>Могут вести</h3>",
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
                'callback' => 'discipline_moevm_add_page_form_ajax_callback',
                'wrapper' => 'can-teach-div',
                'event' => 'change',
            ),
        );
    }

    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Применить'),
    );

    return $form;
}

function discipline_moevm_add_page_form_ajax_callback($form, &$form_state)
{
    return $form['can_teach_block'];
}

function discipline_moevm_add_page_form_submit($form, &$form_state) {
//	debug($form_state['storage']);
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
    $short_name = $form_state['values']['short_name_dis'];

    mysqli_query($mysqli, 'INSERT INTO `discipline`
			(`DisShortName`, 
			`DisFullName`,
			`Chair`)
			SELECT "'. $short_name . '", "' . $full_name . '", `idChair` 
			FROM `chair` 
			WHERE `ChairShortName`= "' . $chair . '"');

    $discipline_result = mysqli_query($mysqli,"SELECT `idDiscipline`
			FROM `discipline`
			WHERE `DisFullName` ='" . $full_name . "'");

    $discipline_id = $discipline_result->fetch_row();
    $discipline_result->close();


    for($i = 1; $i <= $canteach_count; $i++)
    {
        if($form_state['values']['teachers_select' . $i] != 0)
        {
            array_push($chosen_teachers, $form['can_teach_block']['teachers_select' . $i]['#options'][$form_state['values']['teachers_select' . $i]]);
            $name = explode(' ', $chosen_teachers[$i - 1]);

            mysqli_query($mysqli,"INSERT INTO `canteach`
				(`Discipline`, `Teacher`)
				SELECT '" . $discipline_id[0] . "', `idTeacher`
				FROM `teacher`
				WHERE (`Surname` = '" . $name[0] . "' AND
				`FirstName` = '" . $name[1] . "' AND
				`Patronymic` = '" . $name[2] . "')");
        }
    }

    mysqli_close($mysqli);

    drupal_goto("/disciplines/moevm");
}