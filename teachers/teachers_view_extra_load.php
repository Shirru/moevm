<?php

function teachers_view_extra_load_form($form, &$form_state)
{
	$teacher_id = $_GET['id'];
	
	global $user;
	$is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);
    $is_denied = ($is_teacher || $is_student) && !$is_educational;

	$extra_load_rows = get_extra_load_by_teacher($teacher_id);
	$extra_load_kind_options = get_extra_load_kinds();
	$extra_load_kind_rows = get_extra_load_kinds_rows($teacher_id);


	$is_hide_add = isset($form_state['storage']['is_hide_add']) ? $form_state['storage']['is_hide_add'] : true;
	$is_hide_kind = isset($form_state['storage']['is_hide_kind']) ? $form_state['storage']['is_hide_kind'] : true;

	
	$header_kind = array('Нагрузка', 'Норматив', '');

	$form['extra_load'] = array(
		'#type' => 'fieldset',
		'#title' => 'Неаудиторная нагрузка',
		'#collapsible' => TRUE,
		'#collapsed' => TRUE,
		);

	if(!$is_denied)
	{
		$header = array('Вид нагрузки', 'Норматив', 'Семестр', 'Кол-во часов', '');
	}
	else
	{
		$header = array('Вид нагрузки', 'Норматив', 'Семестр', 'Кол-во часов');
	}

	if(!empty($extra_load_rows))
	{

		$form['extra_load']['result_extra_load'] = array(
			'#markup' => theme('table', array('header' => $header, 'rows' => $extra_load_rows)),
			);

	}

	if(!$is_denied)
	{
		$form['extra_load']['add_extra_load'] = array(
			'#type' => 'submit',
			'#value' =>'Добавить нагрузку',
			'#ajax' => array(
	        	'wrapper' => 'extra-load-form-wrapper', 
	        	'callback' => 'teachers_view_extra_load_ajax_callback',
	        	),
		);

		$form['extra_load']['extra_load_kind'] = array(
				'#type' => 'submit',
				'#value' =>'Виды нагрузки',
				'#ajax' => array(
		        	'wrapper' => 'extra-load-kind-form-wrapper', 
		        	'callback' => 'teachers_view_extra_load_kind_ajax_callback',
		        	),
			);

		$form['extra_load']['extra_load_form'] = array(
			'#prefix' => '<div id = "extra-load-form-wrapper">',
			'#suffix' => '</div>',
			);

		if(!$is_hide_add)
		{
			$form['extra_load']['extra_load_form']['kind'] = array(
				'#type' => 'select',
				'#title' => 'Вид нагрузки',
				'#options' => $extra_load_kind_options,
				'#default_value' => 0,
				);

			$form['extra_load']['extra_load_form']['semestr'] = array(
				'#type' => 'textfield',
				'#title' => 'Семестр',
				'#maxlength' => 2,
				'#size' => 5,
				);

			$form['extra_load']['extra_load_form']['hour'] = array(
				'#type' => 'textfield',
				'#title' => 'Кол-во часов',
				'#size' => 5,
				'#maxlength' => 3,
				);

			$form['extra_load']['extra_load_form']['save_extra_load'] = array(
				'#type' => 'submit',
				'#value' => 'Сохранить нагрузку',
				);

			$form['extra_load']['extra_load_form']['hide_extra_load'] = array(
				'#type' => 'submit',
				'#value' => 'Скрыть добавление нагрузки',
				'#ajax' => array(
		        	'wrapper' => 'extra-load-form-wrapper', 
		        	'callback' => 'teachers_view_extra_load_ajax_callback',
		        	),
				);
		}

		$form['extra_load']['extra_load_kind_form'] = array(
			'#prefix' => '<div id = "extra-load-kind-form-wrapper">',
			'#suffix' => '</div>',
			);

		if(!$is_hide_kind)
		{
			$form['extra_load']['extra_load_kind_form']['result_extra_load_kind'] = array(
					'#markup' => theme('table', array('header' => $header_kind, 'rows' => get_extra_load_kinds_rows($teacher_id))),
				);

			$form['extra_load']['extra_load_kind_form']['load_name'] = array(
				'#type' => 'textfield',
				'#title' => 'Название',
				'#size' => 30,
				);

			$form['extra_load']['extra_load_kind_form']['load_standart'] = array(
				'#type' => 'textfield',
				'#title' => 'Норматив',
				'#size' => 30,
				);

			$form['extra_load']['extra_load_kind_form']['add_kind'] = array(
				'#type' => 'submit',
				'#value' =>'Добавить вид нагрузки',
				);

			$form['extra_load']['extra_load_kind_form']['hide_kind'] = array(
				'#type' => 'submit',
				'#value' =>'Скрыть виды нагрузки',
				'#ajax' => array(
		        	'wrapper' => 'extra-load-kind-form-wrapper', 
		        	'callback' => 'teachers_view_extra_load_kind_ajax_callback',
		        	),
				);
		}
	}
	

	return $form;
}

function teachers_view_extra_load_ajax_callback($form, &$form_state)
{
	return $form['extra_load']['extra_load_form'];
}

function teachers_view_extra_load_kind_ajax_callback($form, &$form_state)
{
	return $form['extra_load']['extra_load_kind_form'];
}

function teachers_view_extra_load_form_submit($form, &$form_state)
{
	$teacher_id = $_GET['id'];

	if(isset($form['extra_load']['add_extra_load']['#value']) && $form_state['triggering_element']['#value'] == $form['extra_load']['add_extra_load']['#value'])
	{
		$form_state['storage']['is_hide_add'] = false;
	}

	if(isset($form['extra_load']['extra_load_form']['hide_extra_load']['#value']) && $form_state['triggering_element']['#value'] == $form['extra_load']['extra_load_form']['hide_extra_load']['#value'])
	{
		$form_state['storage']['is_hide_add'] = true;
	}

	if(isset($form['extra_load']['extra_load_form']['save_extra_load']['#value']) && $form_state['triggering_element']['#value'] == $form['extra_load']['extra_load_form']['save_extra_load']['#value'])
	{
		$data = array(
			'teacher_id' => $teacher_id,
			'name' => $form_state['complete form']['extra_load']['extra_load_form']['kind']['#options'][$form_state['values']['kind']],
			'semestr' => $form_state['values']['semestr'],
			'hour' => $form_state['values']['hour'],
			);

		save_extra_load_to_db($data);
		$form_state['storage']['is_hide_add'] = true;
	}

	if(isset($form['extra_load']['extra_load_kind']['#value']) && $form_state['triggering_element']['#value'] == $form['extra_load']['extra_load_kind']['#value'])
	{
		$form_state['storage']['is_hide_kind'] = false;
	}

	if(isset($form['extra_load']['extra_load_kind_form']['hide_kind']['#value']) && $form_state['triggering_element']['#value'] == $form['extra_load']['extra_load_kind_form']['hide_kind']['#value'])
	{
		$form_state['storage']['is_hide_kind'] = true;
	}

	if(isset($form['extra_load']['extra_load_kind_form']['add_kind']['#value']) && $form_state['triggering_element']['#value'] == $form['extra_load']['extra_load_kind_form']['add_kind']['#value'])
	{
		$data = array(
			'name' => $form_state['values']['load_name'],
			'standart' => $form_state['values']['load_standart'],
			);

		save_extra_load_kind_to_db($data);
	}

	$form_state['rebuild'] = TRUE;
}


function get_extra_load_by_teacher($teacher_id)
{
	global $user;
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	$extra_load_rows = array();
	$is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);
    $is_denied = ($is_teacher || $is_student) && !$is_educational;

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$extra_load_result = mysqli_query ($mysqli, "SELECT * FROM `extraload`
		WHERE `Teacher` = '" . $teacher_id . "'");

	foreach ($extra_load_result as $row)
	{
		$extra_load_kind_result = mysqli_query ($mysqli, "SELECT `Name`, `Standart` FROM `extraloadkind`
		WHERE `idExtraLoadKind` = '" . $row['ExtraLoadKind'] . "'");

		$extra_load_kind = $extra_load_kind_result->fetch_assoc();
		$extra_load_kind_result->close();

		if(!$is_denied)
			$extra_load_rows[] = array(
				$extra_load_kind['Name'],
				$extra_load_kind['Standart'],
				$row['Semestr'],
				$row['Hour'],
				"<a href='#' onclick='if(confirm(\"Вы действительно хотите удалить нагрузку?\")){parent.location = \"del?extra_load_id=" . $row['idExtraLoad'] . "&teacher_id=" . $teacher_id . "\";}else return false;'  title='удаление'><img src='/sites/all/pic/delete.png'></a>"
				);
		else
			$extra_load_rows[] = array(
				$extra_load_kind['Name'],
				$extra_load_kind['Standart'],
				$row['Semestr'],
				$row['Hour'],);
	}

	$extra_load_result->close();
	$mysqli->close();

	return $extra_load_rows;
}

function get_extra_load_kinds()
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	$options = array();

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$extra_load_kind_result = mysqli_query ($mysqli, "SELECT `Name` FROM `extraloadkind`");

	foreach ($extra_load_kind_result as $row)
	{
		$options[] = $row['Name'];
	}

	$extra_load_kind_result->close();
	$mysqli->close();

	return $options;
}

function get_extra_load_kinds_rows($teacher_id)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	$rows = array();

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$extra_load_kind_result = mysqli_query ($mysqli, "SELECT * FROM `extraloadkind`");

	foreach ($extra_load_kind_result as $row)
	{
		$rows[] = array(
			$row['Name'],
			$row['Standart'],
			"<a href='#' onclick='if(confirm(\"Вы действительно хотите удалить нагрузку?\")){parent.location = \"del?extra_load_kind_id=" . $row['idExtraLoadKind'] . "&teacher_id=" . $_GET['id'] . "\";}else return false;'  title='удаление'><img src='/sites/all/pic/delete.png'></a>"
			);
	}

	$extra_load_kind_result->close();
	$mysqli->close();

	return $rows;
}

function save_extra_load_to_db($data)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$extra_load_kind_result = mysqli_query ($mysqli, "SELECT `idExtraLoadKind` FROM `extraloadkind`
		WHERE `Name` = '" . $data['name'] . "'");

	$extra_load_kind = $extra_load_kind_result->fetch_assoc();
	$extra_load_kind_result->close();

	$is_success = mysqli_query ($mysqli, "INSERT INTO `extraload`
		(`ExtraLoadKind`, `Teacher`, `Hour`, `Semestr`)
		VALUES ('" . $extra_load_kind['idExtraLoadKind'] . "',
		'" . $data['teacher_id'] . "',
		'" . $data['hour'] . "',
		'" . $data['semestr'] . "')");

	if($is_success)
		drupal_set_message('Данные добавлены успешно!');
	else
		drupal_set_message('Произошла ошибка при сохранении данных', 'error');

	$mysqli->close();
}

function save_extra_load_kind_to_db($data)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$is_success = mysqli_query ($mysqli, "INSERT INTO `extraloadkind`
		(`Name`, `Standart`)
		VALUES ('" . $data['name'] . "',
		'" . $data['standart'] . "')");

	if($is_success)
		drupal_set_message('Данные добавлены успешно!');
	else
		drupal_set_message('Произошла ошибка при сохранении данных', 'error');

	$mysqli->close();
}