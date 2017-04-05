<?php

function teachers_page()
{
	if(isset($_GET['del']))
	{
		$teacher_id = $_GET['id'];
		delete_teacher($teacher_id);
		drupal_goto('teachers');
	}

	global $user;
    $is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);
    $is_denied = ($is_teacher || $is_student) && !$is_educational;

	$output = "";
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

  	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
  	mysqli_query ($mysqli, "SET NAMES `utf8`");

  	$teachers_result = $mysqli->query("SELECT *
                 FROM teacher ORDER BY `Surname`, `FirstName`, `Patronymic`");

	$mysqli->close();

	if($is_denied)
	{
		$header = array('', 'Фамилия', 'Имя', 'Отчество', 'Должность', 'Степень', 'Звание', 'Состояние');
	}
	else
	{
		$header = array('', 'Фамилия', 'Имя', 'Отчество', 'Должность', 'Степень', 'Звание', 'Состояние', '');
		$output .= "<div style='text-align: right'>
		<a href='teachers/add'  title='Добавить нового преподавателя'> Добавить </a>
		</div>";
	}
	
	$rows = array();

	foreach($teachers_result as $row) 
	{
		if($is_denied)
		{
			$rows[] = array("<a href='teachers/view?id=" . $row ["idTeacher"] . "'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
			$row["Surname"], $row["FirstName"], $row["Patronymic"], 
			$row["Position"], $row["Degree"], $row["Rank"],
			$row["Condition"],
			);
		}
		else 
		{
			$rows[] = array("<a href='teachers/view?id=" . $row ["idTeacher"] . "'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
			$row["Surname"], $row["FirstName"], $row["Patronymic"], 
			$row["Position"], $row["Degree"], $row["Rank"],
			$row["Condition"],
			"<a href='#' onclick='if(confirm(\"Вы действительно хотите удалить преподавателя?\")){parent.location = \"teachers?del=true&id=" . $row ["idTeacher"] . "\";}else return false;'  title='удаление'><img src='/sites/all/pic/delete.png'></a>");
		}
        
      }

	$teachers_result->close();
	$output .= theme('table', array('header' => $header, 'rows' => $rows));

	return $output;
}

function delete_teacher($id)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

  	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
  	mysqli_query ($mysqli, "SET NAMES `utf8`");

  	$is_success = mysqli_query ($mysqli, "DELETE FROM `teacher`
  		WHERE `idTeacher` = '" . $id . "'");

  	if($is_success)
  		drupal_set_message('Данные успешно удалены!');
  	else
  		drupal_set_message('Произошла ошибка при удалении данных','error');
}

?>
