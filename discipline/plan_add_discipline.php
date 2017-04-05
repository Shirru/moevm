<?php

function disciplines_plan_add_discipline_form($form, &$form_state)
{
	$curriculum_id = $_GET['id'];
	$discipline_options = get_disciplines();

	$form['Index'] = array(
		'#type' => 'textfield',
		'#size' => 50,
		'#title' => 'Индекс',
		);

	$form['DisciplineName'] = array(
		'#type' => 'select',
		'#options' => $discipline_options,
		'#title' => 'Название',
		'#default_value' => 0,
		);

	$form['Semester'] = array(
		'#type' => 'textfield',
		'#size' => 50,
		'#title' => 'Семестр',
		);

	$form['hour_and_control'] = array(
		'#prefix' => '<table>',
		'#suffix' => '</table>',
		);

	$form['hour_and_control']['row'] = array(
			'#prefix' => '<tr>',
			'#suffix' => '</tr>',
			);

	$form['hour_and_control']['row']['Exam'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'Экз',
		'#default_value' => 0,
		);

	$form['hour_and_control']['row']['CreditW/OGrade'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'За',
		'#default_value' => 0,
		);

	$form['hour_and_control']['row']['CreditWithGrade'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'ЗаО',
		'#default_value' => 0,
		);

	$form['hour_and_control']['row']['Lecture'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'Лек',
		'#default_value' => 0,
		);

	$form['hour_and_control']['row']['Lab'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'Лаб',
		'#default_value' => 0,
		);

	$form['hour_and_control']['row']['Practice'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'Практ',
		'#default_value' => 0,
		);

	$form['hour_and_control']['row']['Solo'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'СРС',
		'#default_value' => 0,
		);

	$form['hour_and_control']['row']['CourseProject'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'КП',
		'#default_value' => 0,
		);

	$form['hour_and_control']['row']['CourseWork'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'КР',
		'#default_value' => 0,
		);

	$form['hour_and_control']['row']['Zet'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'ЗЕТ',
		'#default_value' => 0,
		);

	$form['save'] = array(
		'#type' => 'submit',
		'#value' => 'Сохранить',
		);

	$form['back'] = array(
		'#type' => 'submit',
		'#value' => 'Назад',
		);

	return $form;
}

function disciplines_plan_add_discipline_form_submit($form, &$form_state)
{
	$curriculum_id = $_GET['id'];
	if(isset($form['back']['#value']) && $form_state['triggering_element']['#value'] == $form['back']['#value'])
	{
		drupal_goto('disciplines/plan/view', array(
			'query' => array('id'=>$curriculum_id,)));
	}

	if(isset($form['save']['#value']) && $form_state['triggering_element']['#value'] == $form['save']['#value'])
	{
		$data = array(
			'Index' => $form_state['values']['Index'],
			'Name' => $form_state['complete form']['DisciplineName']['#options'][$form_state['values']['DisciplineName']],
			'Curriculum' => $curriculum_id,
			'Sem' => $form_state['values']['Semester'],
			'Exam' => $form_state['values']['Exam'],
			'Za' => $form_state['values']['CreditW/OGrade'],
			'ZaO' => $form_state['values']['CreditWithGrade'],
			'Lec' => $form_state['values']['Lecture'],
			'Lab' => $form_state['values']['Lab'],
			'Pract' => $form_state['values']['Practice'],
			'Solo' => $form_state['values']['Solo'],
			'CP' => $form_state['values']['CourseProject'],
			'CW' => $form_state['values']['CourseWork'],
			'Zet' => $form_state['values']['Zet'],
			);

		save_insert_curriculum_discipline($data);
	}
}

function get_disciplines()
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	$disciplines = array();

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$discipline_result = mysqli_query ($mysqli, "SELECT `DisFullName` FROM `discipline`");

	foreach ($discipline_result as $row) 
	{
		$disciplines[] = $row['DisFullName'];
	}

	$discipline_result->close();
	$mysqli->close();

	return $disciplines;
}

function save_insert_curriculum_discipline($data)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	$chairs = array();

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$discipline_result = mysqli_query ($mysqli, "SELECT `idDiscipline` FROM `discipline`
		WHERE `DisFullName` = '" . $data['Name'] . "'");

	$discipline = $discipline_result->fetch_assoc();
	$discipline_result->close();

	$total = intval($data['Lec']) + intval($data['Lab']) + intval($data['Pract']) + intval($data['Solo']);

	$is_success = mysqli_query ($mysqli, "INSERT INTO `curriculumdiscipline` 
		(`DisIndex`, `Discipline`, `Curriculum`,
		 `Exam`, `CreditW/OGrade`, `CreditWithGrade`, `Lecture`, `Lab`,
		 `Practice`, `Solo`, `CourseProject`, `CourseWork`, `Zet`, `Total`, `Semester`)
		 VALUES (
		 '" . $data['Index'] ."',
		 '" . $discipline['idDiscipline'] ."',
		 '" . $data['Curriculum'] ."',
		 '" . $data['Exam'] ."',
		 '" . $data['Za'] ."',
		 '" . $data['ZaO'] ."',
		 '" . $data['Lec'] ."',
		 '" . $data['Lab'] ."',
		 '" . $data['Pract'] ."',
		 '" . $data['Solo'] ."',
		 '" . $data['CP'] ."',
		 '" . $data['CW'] ."',
		 '" . $data['Zet'] ."',
		 '" . $total ."',
		 '" . $data['Sem'] ."' 
		 )");

	$mysqli->close();

	if($is_success)
		drupal_set_message('Данные добавлены успешно!');
	else
		drupal_set_message('Произошла ошибка при сохранении данных','error');
}