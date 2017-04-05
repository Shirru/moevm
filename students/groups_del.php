<?php

function groups_del()
{
	if(isset($_GET['dismo']) && isset($_GET['id']))
	{
		$id = $_GET['id'];
		delete_group_by_id($id);
		drupal_goto('groups/moevm');
	}

	if(isset($_GET['student_id']) && isset($_GET['group_id']))
	{
		$student_id = $_GET['student_id'];
		$group_id = $_GET['group_id'];
		delete_student($student_id);
		drupal_goto('groups/moevm/view', array(
			'query' => array('id'=>$group_id,)));
	}

	if(isset($_GET['other_group_id']))
	{
		$other_group_id = $_GET['other_group_id'];
		delete_group_by_id($other_group_id);
		drupal_goto('groups/other');
	}
}

function delete_group_by_id($id)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$is_success = mysqli_query ($mysqli, "DELETE FROM `group`
		WHERE `idGroup` = '" . $id . "'");

	if($is_success)
		drupal_set_message('Группа удалена успешно!');
	else
		drupal_set_message('Произошла ошибка при удалении данных', 'error');
}

function delete_student($student_id)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$is_success = mysqli_query ($mysqli, "DELETE FROM `student`
		WHERE `idStudent` = '" . $student_id . "'");

	if($is_success)
		drupal_set_message('Студент удален успешно!');
	else
		drupal_set_message('Произошла ошибка при удалении данных', 'error');
}