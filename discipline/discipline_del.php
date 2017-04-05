<?php

function disciplines_del()
{
	if(isset($_GET['dismo']) && isset($_GET['dis_id']))
	{
		$dis_id = $_GET['dis_id'];
		delete_discipline($dis_id);
		drupal_goto('disciplines/moevm');
	}

	if(isset($_GET['dis_id']) && isset($_GET['cur_id']))
	{
		$dis_id = $_GET['dis_id'];
		$cur_id = $_GET['cur_id'];
		delete_curriculum_discipline($dis_id);
		drupal_goto('disciplines/plan/view', array(
			'query' => array('id'=>$cur_id,)));
	}

	if(isset($_GET['can_teach_teacher_id']) && isset($_GET['dis_id']))
	{
		$can_teach_teacher_id = $_GET['can_teach_teacher_id'];
		$dis_id = $_GET['dis_id'];
		delete_can_teach_teacher_from_discipline($dis_id, $can_teach_teacher_id);
		drupal_goto('disciplines/moevm/view', array(
			'query' => array('dis'=>$dis_id,)));
	}

	if(isset($_GET['cur_id']) && isset($_GET['curriculum']))
	{
		$cur_id = $_GET['cur_id'];
		delete_curriculum($cur_id);
		drupal_goto('disciplines/plan');
	}
}

function delete_discipline($dis_id)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$is_success = mysqli_query ($mysqli, "DELETE FROM `discipline`
		WHERE `idDiscipline` = '" . $dis_id . "'");

	if($is_success)
		drupal_set_message('Дисциплина удалена успешно!');
	else
		drupal_set_message('Произошла ошибка при удалении данных', 'error');
}

function delete_can_teach_teacher_from_discipline($dis_id, $can_teach_teacher_id)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$is_success = mysqli_query ($mysqli, "DELETE FROM `canteach`
		WHERE (`Discipline` = '" . $dis_id . "' AND
		`Teacher` = '" . $can_teach_teacher_id . "')");

	if($is_success)
		drupal_set_message('Запись "может вести" удалена успешно!');
	else
		drupal_set_message('Произошла ошибка при удалении данных', 'error');
}

function delete_curriculum_discipline($dis_id)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$is_success = mysqli_query ($mysqli, "DELETE FROM `curriculumdiscipline`
		WHERE `idCurriculumDiscipline` = '" . $dis_id . "'");

	if($is_success)
		drupal_set_message('Дисциплина УП удалена успешно!');
	else
		drupal_set_message('Произошла ошибка при удалении данных', 'error');
}

function delete_curriculum($cur_id)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$is_success = mysqli_query ($mysqli, "DELETE FROM `curriculum`
		WHERE `idCurriculum` = '" . $cur_id . "'");

	if($is_success)
		drupal_set_message('УП удален успешно!');
	else
		drupal_set_message('Произошла ошибка при удалении данных', 'error');
}