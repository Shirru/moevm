<?php

function teachers_del()
{
	if(isset($_GET['hall_load_id']) && isset($_GET['teacher_id']))
	{
		$hall_load_id = $_GET['hall_load_id'];
		$teacher_id = $_GET['teacher_id'];
		delete_hall_load_from_teacher($hall_load_id);
		drupal_goto('teachers/view', array(
			'query' => array('id'=>$teacher_id,)));
	}

	if(isset($_GET['extra_load_id']) && isset($_GET['teacher_id']))	
	{
		$extra_load_id = $_GET['extra_load_id'];
		$teacher_id = $_GET['teacher_id'];
		delete_extra_load_from_teacher($extra_load_id);
		drupal_goto('teachers/view', array(
			'query' => array('id'=>$teacher_id,)));
	}

	if(isset($_GET['extra_load_kind_id']) && isset($_GET['teacher_id']))	
	{
		$extra_load_kind_id = $_GET['extra_load_kind_id'];
		$teacher_id = $_GET['teacher_id'];
		delete_extra_load_kind_from_teacher($extra_load_kind_id);
		drupal_goto('teachers/view', array(
			'query' => array('id'=>$teacher_id,)));
	}

	if(isset($_GET['can_teach_dis_id']) && isset($_GET['teacher_id']))	
	{
		$can_teach_dis_id = $_GET['can_teach_dis_id'];
		$teacher_id = $_GET['teacher_id'];
		delete_canteach_discipline($can_teach_dis_id, $teacher_id);
		drupal_goto('teachers/view', array(
			'query' => array('id'=>$teacher_id,)));
	}
}

function delete_hall_load_from_teacher($hall_load_id)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	$hall_load_rows = array();

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$is_success = mysqli_query ($mysqli, "DELETE FROM `hallload`
		WHERE `idHallLoad` = '" . $hall_load_id . "'");

	if($is_success)
		drupal_set_message('Аудиторная нагрузка удалена успешно!');
	else
		drupal_set_message('Произошла ошибка при удалении данных', 'error');
}

function delete_extra_load_from_teacher($extra_load_id)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	$hall_load_rows = array();

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$is_success = mysqli_query ($mysqli, "DELETE FROM `extraload`
		WHERE `idExtraLoad` = '" . $extra_load_id . "'");

	if($is_success)
		drupal_set_message('Неаудиторная нагрузка удалена успешно!');
	else
		drupal_set_message('Произошла ошибка при удалении данных', 'error');
}

function delete_extra_load_kind_from_teacher($extra_load_kind_id)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	$hall_load_rows = array();

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$is_success = mysqli_query ($mysqli, "DELETE FROM `extraloadkind`
		WHERE `idExtraLoadKind` = '" . $extra_load_kind_id . "'");

	if($is_success)
		drupal_set_message('Вид неаудиторной нагрузки удален успешно!');
	else
		drupal_set_message('Произошла ошибка при удалении данных', 'error');
}

function delete_canteach_discipline($can_teach_dis_id, $teacher_id)
{

	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$is_success = mysqli_query ($mysqli, "DELETE FROM `canteach`
		WHERE (`Discipline` = '" . $can_teach_dis_id . "' AND `Teacher` = '" . $teacher_id . "')");

	if($is_success)
		drupal_set_message('Запись "может вести" удалена успешно!');
	else
		drupal_set_message('Произошла ошибка при удалении данных', 'error');
}