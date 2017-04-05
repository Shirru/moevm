<?php

function teachers_view_hall_load_form($form, &$form_state)
{
	global $user;
    $is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);
    $is_denied = ($is_teacher || $is_student) && !$is_educational;

	$teacher_id = $_GET['id'];
	$hall_load_rows = get_hall_load_by_teacher($teacher_id);
	
	if($is_denied)
		$header = array('Дисциплина', 'Группа', 'Сем', 'Лек', 'Практ',
	 'ЛР', 'КР', 'КП', 'Экз', 'ЗаО', 'За');
	else
		$header = array('Дисциплина', 'Группа', 'Сем', 'Лек', 'Практ',
	 'ЛР', 'КР', 'КП', 'Экз', 'ЗаО', 'За', '');

	$is_hide = isset($form_state['storage']['is_hide']) ? $form_state['storage']['is_hide'] : true;

	$form['hall_load'] = array(
		'#type' => 'fieldset',
		'#title' => 'Аудиторная нагрузка',
		'#collapsible' => TRUE,
		'#collapsed' => TRUE,
		);

	if(!empty($hall_load_rows))
		$form['hall_load']['result_load'] = array(
			'#markup' => theme('table', array('header' => $header, 'rows' => $hall_load_rows)),
			);

	if(!$is_denied)
	{
		$form['hall_load']['add_hall_load'] = array(
			'#type' => 'submit',
			'#value' =>'Добавить',
			'#ajax' => array(
	        	'wrapper' => 'hall-load-form-wrapper', 
	        	'callback' => 'teachers_view_hall_load_ajax_callback',
	        	),
		);

		$form['hall_load']['hall_load_form'] = array(
			'#prefix' => '<div id = "hall-load-form-wrapper"><table>',
			'#suffix' => '</table></div>',
			);

		$discipline_options = get_canteach_disciplines($teacher_id);

		$form_state['storage']['discipline_options'] = isset($form_state['storage']['discipline_options']) ?
		$form_state['storage']['discipline_options'] : $discipline_options;

		if(!$is_hide)
		{
			$form['hall_load']['hall_load_form']['discipline'] = array(
				'#type' => 'select',
				'#title' => 'Дисциплина',
				'#options' => $form_state['storage']['discipline_options'],
				'#default_value' => 0,
				);

			$form['hall_load']['hall_load_form']['group'] = array(
				'#type' => 'select',
				'#title' => 'Группа',
				'#options' => get_groups(),
				'#default_value' => 0,
				);

			$form['hall_load']['hall_load_form']['table'] = array(
				'#prefix' => '<table>',
				'#suffix' => '</table>',
				);

			$form['hall_load']['hall_load_form']['table']['row'] = array(
				'#prefix' => '<tr>',
				'#suffix' => '</tr>',
				);

			$form['hall_load']['hall_load_form']['table']['row']['Sem'] = array(
				'#prefix' => '<td>',
				'#suffix' => '</td>',
				'#type' => 'textfield',
				'#title' => 'Семестр',
				'#size' => 5,
				);

			$form['hall_load']['hall_load_form']['table']['row']['Lec'] = array(
				'#prefix' => '<td>',
				'#suffix' => '</td>',
				'#type' => 'textfield',
				'#title' => 'Лекции',
				'#size' => 5,
				);

			$form['hall_load']['hall_load_form']['table']['row']['Pract'] = array(
				'#prefix' => '<td>',
				'#suffix' => '</td>',
				'#type' => 'textfield',
				'#title' => 'Практика',
				'#size' => 5,
				);

			$form['hall_load']['hall_load_form']['table']['row']['Lab'] = array(
				'#prefix' => '<td>',
				'#suffix' => '</td>',
				'#type' => 'textfield',
				'#title' => 'ЛР',
				'#size' => 5,
				);

			$form['hall_load']['hall_load_form']['table']['row']['CourseWork'] = array(
				'#prefix' => '<td>',
				'#suffix' => '</td>',
				'#type' => 'textfield',
				'#title' => 'КР',
				'#size' => 5,
				);

			$form['hall_load']['hall_load_form']['table']['row']['CourseProject'] = array(
				'#prefix' => '<td>',
				'#suffix' => '</td>',
				'#type' => 'textfield',
				'#title' => 'КП',
				'#size' => 5,
				);

			$form['hall_load']['hall_load_form']['table']['row']['Exam'] = array(
				'#prefix' => '<td>',
				'#suffix' => '</td>',
				'#type' => 'textfield',
				'#title' => 'Экзамен',
				'#size' => 5,
				);

			$form['hall_load']['hall_load_form']['table']['row']['CreditWithGrade'] = array(
				'#prefix' => '<td>',
				'#suffix' => '</td>',
				'#type' => 'textfield',
				'#title' => 'ЗаО',
				'#size' => 5,
				);

			$form['hall_load']['hall_load_form']['table']['row']['CreditW/OGrade'] = array(
				'#prefix' => '<td>',
				'#suffix' => '</td>',
				'#type' => 'textfield',
				'#title' => 'За',
				'#size' => 5,
				);

			$form['hall_load']['hall_load_form']['save_load'] = array(
				'#type' => 'submit',
				'#value' => 'Сохранить',

				);

			$form['hall_load']['hall_load_form']['hide_hall_load'] = array(
				'#type' => 'submit',
				'#value' => 'Скрыть добавление ауд. нагрузки',
				'#ajax' => array(
		        	'wrapper' => 'hall-load-form-wrapper', 
		        	'callback' => 'teachers_view_hall_load_ajax_callback',
		        	),
				);

		}
	}
	

	return $form;
}

function teachers_view_hall_load_ajax_callback($form, &$form_state)
{
	return $form['hall_load']['hall_load_form'];
}

function teachers_view_hall_load_form_submit($form, &$form_state)
{
	$teacher_id = $_GET['id'];
	if(isset($form['hall_load']['add_hall_load']['#value']) && $form_state['triggering_element']['#value'] == $form['hall_load']['add_hall_load']['#value'])
	{
		$form_state['storage']['is_hide'] = false;
	}

	if(isset($form['hall_load']['hall_load_form']['hide_hall_load']['#value']) && $form_state['triggering_element']['#value'] == $form['hall_load']['hall_load_form']['hide_hall_load']['#value'])
	{
		$form_state['storage']['is_hide'] = true;
	}

	if(isset($form['hall_load']['hall_load_form']['save_load']['#value']) && $form_state['triggering_element']['#value'] == $form['hall_load']['hall_load_form']['save_load']['#value'])
	{
		$data = array(
			'discipline' => $form_state['complete form']['hall_load']['hall_load_form']['discipline']['#options'][$form_state['values']['discipline']],
			'group' => $form_state['complete form']['hall_load']['hall_load_form']['group']['#options'][$form_state['values']['group']],
			'teacher_id' => $teacher_id,
			'sem' => $form_state['values']['Sem'],
			'lec' => $form_state['values']['Lec'],
			'pract' => $form_state['values']['Pract'],
			'lab' => $form_state['values']['Lab'],
			'cw' => $form_state['values']['CourseWork'],
			'cp' => $form_state['values']['CourseProject'],
			'exam' => $form_state['values']['Exam'],
			'CreditWithGrade' => $form_state['values']['CreditWithGrade'],
			'CreditW/OGrade' => $form_state['values']['CreditW/OGrade'],
			);

		save_hall_load_to_db($data);
		$form_state['storage']['is_hide'] = true;
	}

	$form_state['rebuild'] = TRUE;
}

function get_hall_load_by_teacher($teacher_id)
{
	global $user;
    $is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);
    $is_denied = ($is_teacher || $is_student) && !$is_educational;
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	$hall_load_rows = array();

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$hall_load_result = mysqli_query ($mysqli, "SELECT * FROM `hallload`
		WHERE `Teacher` = '" . $teacher_id . "'");

	foreach ($hall_load_result as $row)
	{
		$discipline_result = mysqli_query ($mysqli, "SELECT `DisFullName` FROM `discipline`
		WHERE `idDiscipline` = '" . $row['Discipline'] . "'");

		$discipline = $discipline_result->fetch_assoc();
		$discipline_result->close();

		$group_result = mysqli_query ($mysqli, "SELECT `GroupNum` FROM `group`
		WHERE `idGroup` = '" . $row['Group'] . "'");

		$group = $group_result->fetch_assoc();
		$group_result->close();

		if(!$is_denied)
			$hall_load_rows[] = array(
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
				"<a href='#' onclick='if(confirm(\"Вы действительно хотите удалить нагрузку?\")){parent.location = \"del?hall_load_id=" . $row['idHallLoad'] . "&teacher_id=" . $teacher_id . "\";}else return false;'  title='удаление'><img src='/sites/all/pic/delete.png'></a>"
				);
		else
			$hall_load_rows[] = array(
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

	return $hall_load_rows;
}

function get_groups()
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	$groups = array();

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$group_result = mysqli_query ($mysqli, "SELECT `GroupNum` FROM `group`");

	foreach ($group_result as $group) 
	{
		$groups[] = $group['GroupNum'];
	}

	$group_result->close();
	$mysqli->close();

	return $groups;
}

function save_hall_load_to_db($data)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$discipline_result = mysqli_query ($mysqli, "SELECT `idDiscipline` FROM `discipline`
		WHERE `DisFullName` = '" . $data['discipline'] . "'");

	$discipline = $discipline_result->fetch_assoc();
	$discipline_result->close();

	$group_result = mysqli_query ($mysqli, "SELECT `idGroup` FROM `group`
		WHERE `GroupNum` = '" . $data['group'] . "'");

	$group = $group_result->fetch_assoc();
	$group_result->close();

	$is_success = mysqli_query ($mysqli, "INSERT INTO `hallload`
		(`Discipline`, `Teacher`, `Group`, `Semestr`, `Lec`, `Pract`, `Lab`,
		 `CourseWork`, `CourseProject`, `Exam`, `CreditWithGrade`, `CreditW/OGrade`)
		 VALUES(
		 '" . $discipline['idDiscipline'] . "',
		 '" . $data['teacher_id'] . "',
		 '" . $group['idGroup'] . "',
		 '" . $data['sem'] . "',
		 '" . $data['lec'] . "',
		 '" . $data['pract'] . "',
		 '" . $data['lab'] . "',
		 '" . $data['cw'] . "',
		 '" . $data['cp'] . "',
		 '" . $data['exam'] . "',
		 '" . $data['CreditWithGrade'] . "',
		 '" . $data['CreditW/OGrade'] . "')");

	if($is_success)
		drupal_set_message('Данные добавлены успешно!');
	else
		drupal_set_message('Произошла ошибка при сохранении данных', 'error');

	$mysqli->close();
}

function get_canteach_disciplines($teacher_id)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	$disciplines = array();

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$disciplines_result = mysqli_query ($mysqli, "SELECT `DisFullName` FROM `discipline`
	 WHERE `idDiscipline` IN 
	 	(SELECT `Discipline` FROM `canteach` 
	 		WHERE `teacher` = '" . $teacher_id . "')");


	foreach ($disciplines_result as $discipline) 
	{
		$disciplines[] = $discipline['DisFullName'];
	}

	$disciplines_result->close();
	$mysqli->close();

	return $disciplines;
}