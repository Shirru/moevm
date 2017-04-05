<?php

function groups_moevm_add_student_form($form, &$form_state)
{
	$group_id = $_GET['id'];

	$form['column_left'] = array(
		'#type' => 'container',
		'#attributes' => array(
			'class' => array('column-left'),
			'style' => array('float: left'),
			),
		);

	$form['column_left']['credit_book'] = array(
		'#type' => 'textfield',
		'#title' => 'Номер зачетной книжки',
		'#size' => 30,
		);

	$form['column_left']['surname'] = array(
		'#type' => 'textfield',
		'#title' => 'Фамилия',
		'#size' => 30,
		);

	$form['column_left']['first_name'] = array(
		'#type' => 'textfield',
		'#title' => 'Имя',
		'#size' => 30,
		);

	$form['column_left']['patronymic'] = array(
		'#type' => 'textfield',
		'#title' => 'Отчество',
		'#size' => 30,
		);

	$form['column_right'] = array(
		'#type' => 'container',
		'#attributes' => array(
			'class' => array('column-right'),
			'style' => array('float: right'),
			),
		);

	$form['column_right']['group'] = array(
		'#type' => 'textfield',
		'#title' => 'Номер группы',
		'#size' => 30,
		'#default_value' => get_group_num_by_id($group_id),
		'#attributes' => array(
			'readonly' => array('readonly')),
		);	

	$form['column_right']['enrollment_date'] = array(
		'#type' => 'date',
		'#title' => 'Дата зачисления',
		);

	$form['column_right']['email'] = array(
		'#type' => 'textfield',
		'#title' => 'E-mail',
		'#size' => 30,
		);

	$form['column_left']['submit'] = array(
		'#type' => 'submit',
		'#value' => 'Добавить',
		);

	return $form;
}

function groups_moevm_add_student_form_submit($form, &$form_state)
{
	$group_id = $_GET['id'];
	$server = 'localhost';
    $username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $is_success = mysqli_query ($mysqli, "INSERT INTO `student`
    	(`RecordBookNum`, `Surname`, `FirstName`, `Patronymic`, `Group`, `E-mail`, `EnrollmentDate`)
    	VALUES ('" . $form_state['values']['credit_book'] . "',
    	'" . $form_state['values']['surname'] . "',
    	'" . $form_state['values']['first_name'] . "',
    	'" . $form_state['values']['patronymic'] . "',
    	'" . $group_id . "',
    	'" . $form_state['values']['email'] . "',
    	'" . $form_state['values']['enrollment_date']['year'] . '-' . $form_state['values']['enrollment_date']['month'] . '-' . $form_state['values']['enrollment_date']['day']. "')");

    if($is_success)
    	drupal_set_message('Данные добавлены успешно!');
    else
    	drupal_set_message('Ошибка при сохранении данных', 'error');

    drupal_goto('groups/moevm/view', array(
			'query' => array('id'=>$group_id,)));
}

function get_group_num_by_id($group_id)
{
	$server = 'localhost';
    $username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $group_result = mysqli_query ($mysqli, "SELECT `GroupNum` FROM `group`
    	WHERE `idGroup` = '" . $group_id . "'");

    $group = $group_result->fetch_assoc();
    $group_result->close();
    $mysqli->close();

    return $group['GroupNum'];
}