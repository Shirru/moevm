<?php

function teachers_view_canteach_form($form, &$form_state)
{
	global $user;
    $is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);
    $is_denied = ($is_teacher || $is_student) && !$is_educational;

	$teacher_id = $_GET['id'];

	$disciplines = teachers_view_canteach_disciplines();

	$table = teachers_view_canteach_create_table($teacher_id);

	$form['can_teach'] = array(
		'#type' => 'fieldset',
		'#collapsible' => TRUE, 
		'#collapsed' => TRUE, 
		'#title' => 'Может вести'
		);

	$form['can_teach']['can_teach_table'] = array(
  		'#markup' => $table,	
		);

	if(!$is_denied)
	{
		$form['can_teach']['can_teach_add_text'] = array(
  			'#markup' => "<h3>Добавить дисциплину</h3>",	
		);

	  	$form['can_teach']['add_discipline'] = array(
	  		'#prefix' => '<div id = "add-discipline-div">',
	    	'#suffix' => '</div>',
	    	'#type' => 'fieldset',
	  	);

	  	if(isset($form_state['values']))	
	  	{
	  		$discipline_count = $form_state['storage']['count'];
	  		if($form_state['values']['discipline_select' . $discipline_count] != 0)
	  		{
	  			$form_state['storage']['count'] ++;
	  		}

	  		$discipline_count = $form_state['storage']['count'];
	  	}
	  	else
	  	{
	  		$discipline_count = 1;
	  		$form_state['storage']['count'] = 1;
	  	}	

		for ($i = 1; $i <= $discipline_count; $i++) 
		{
		    $form['can_teach']['add_discipline']['discipline_select' . $i] = array(
		      	'#type' => 'select', 
		      	'#options' => $disciplines,
	    	  	'#default_value' => 0,
	    	  	'#ajax' => array(
				    // Функция, которая сработает при выборе значения в списке,
				    // и которая должна вернуть новую часть формы
				    'callback' => 'teachers_view_canteach_form_ajax_callback',
				    // Id html элемента, в который будет выведена часть формы
				    'wrapper' => 'add-discipline-div',
				    ),
		    );
		}

		$form['can_teach']['submit'] = array(
		  '#type' => 'submit',
		  '#value' => t('Сохранить'),
		);
	}


	

	return $form;
}

function teachers_view_canteach_create_table($teacher_id)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	global $user;
    $is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);
    $is_denied = ($is_teacher || $is_student) && !$is_educational;

    if(!$is_denied)
		$header = array('Полное название', 'Краткое название', '');
	else
		$header = array('Полное название', 'Краткое название',);
	$table_rows = array();

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	// Дисциплины, которые может вести преподаватель
	$dis_result = $mysqli->query("SELECT `DisFullName`, `DisShortName`, `idDiscipline`
		FROM discipline 
		WHERE `idDiscipline` IN
		(SELECT `Discipline` FROM canteach
		WHERE `Teacher` = '" . $teacher_id . "') ");

    $mysqli->close();

    if($dis_result) {
        foreach ($dis_result as $row) {
            if($is_denied) {
                $table_rows[] = array($row['DisFullName'],
                    $row['DisShortName'],);
            }
            else {
                $table_rows[] = array($row['DisFullName'],
                    $row['DisShortName'],
                    "<a href='#' onclick='if(confirm(\"Вы действительно хотите удалить запись `может вести`?\"))
                    {parent.location = \"del?can_teach_dis_id=" . $row['idDiscipline'] . "&teacher_id=" . $teacher_id
                    . "\";}else return false;'  title='удалить'><img src='/sites/all/pic/delete.png'></a>");
            }
        }

        $dis_result -> close();
    }

	// Создаем таблицу
	$table = theme('table', array('header' => $header, 'rows' => $table_rows));

	if(!empty($table_rows))
		return $table;
	else
		return '';	
}

function teachers_view_canteach_disciplines()
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

	$disciplines = array();
	array_push($disciplines, "Выбрать дисциплину");

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$discipline_result = $mysqli->query("SELECT `DisFullName`
		FROM discipline
		WHERE `Chair` = (SELECT `idChair` FROM chair WHERE `ChairNum` = 14) ORDER BY `DisFullName`
		");

	$mysqli->close();

	foreach ($discipline_result as $row) 
	{
		array_push($disciplines, $row['DisFullName']);
	}

	$discipline_result->close();

	return $disciplines;
}

function teachers_view_canteach_form_ajax_callback($form, &$form_state) 
{
  	return $form['can_teach']['add_discipline'];
}

function  teachers_view_canteach_form_submit($form, &$form_state) 
{
	$teacher_id = $_GET['id'];
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	$chair_num = 14;

	$discipline_count = isset($form_state['storage']) ? $form_state['storage']['count'] : 1;

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	for($i = 1; $i <= $discipline_count; $i++)
  	{
  		if($form_state['values']['discipline_select' . $i] != 0)
  		{
  			$chosen_discipline =  $form['can_teach']['add_discipline']['discipline_select' . $i]['#options'][$form_state['values']['discipline_select' . $i]];

  			mysqli_query($mysqli,"INSERT INTO `canteach`
				(`Discipline`, `Teacher`)
				SELECT `idDiscipline`, '" . $teacher_id . "'
				FROM `discipline`
				WHERE (`DisFullName` = '" . $chosen_discipline . "')");
  		}	
  	}

	mysqli_close($mysqli);

}