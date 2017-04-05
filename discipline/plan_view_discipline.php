<?php

function disciplines_plan_view_discipline_form($form, &$form_state)
{
	global $user;
	$is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);
    $is_denied = ($is_teacher || $is_student) && !$is_educational;

	$dis_id = $_GET['id'];
	$discipline = get_curriculum_discipline_by_id($dis_id);
	$curriculum_options = get_curriculum_nums();
	$chair_options = get_chairs();

	$uri = get_work_program_path($dis_id);
	if(!empty($uri))
	{
		$file_name = drupal_basename($uri);
		$url = file_create_url($uri);
	}


	for($i = 0; $i < count($curriculum_options); $i++)
	{
		if($discipline['CurriculumNum'] == $curriculum_options[$i])
		{
			$curriculum_default_value = $i;
			break;
		}
	}


	for($i = 0; $i < count($chair_options); $i++)
	{
		if($discipline['ChairFullName'] == $chair_options[$i])
		{
			$chair_default_value = $i;
			break;
		}
	}

	if($is_denied)
    {
        $readonly = 'readonly'; 
    }
    else
    {
        $readonly = '';
    }

	$form['Index'] = array(
		'#type' => 'textfield',
		'#size' => 50,
		'#title' => 'Индекс',
		'#attributes' => array(
            $readonly => array($readonly),),
		'#default_value' => $discipline['DisIndex'],
		);

	$form['DisciplineName'] = array(
		'#type' => 'textfield',
		'#size' => 50,
		'#title' => 'Название',
		'#attributes' => array(
            $readonly => array($readonly),),
		'#default_value' => $discipline['DisciplineName'],
		);

	$form['Semester'] = array(
		'#type' => 'textfield',
		'#size' => 50,
		'#title' => 'Семестр',
		'#attributes' => array(
            $readonly => array($readonly),),
		'#default_value' => $discipline['Semester'],
		);

	$form['CurriculumNum'] = array(
		'#type' => 'select',
		'#options' => $curriculum_options,
		'#title' => 'Номер УП',
		'#attributes' => array(
            $readonly => array($readonly),),
		'#default_value' => $curriculum_default_value,
		);

	$form['ChairShortName'] = array(
		'#type' => 'select',
		'#options' => $chair_options,
		'#title' => 'Кафедра',
		'#attributes' => array(
            $readonly => array($readonly),),
		'#default_value' => $chair_default_value,
		);

	if(!empty($uri))
	{
		$ext = pathinfo($file_name, PATHINFO_EXTENSION);
		$form['WorkProgram'] = array(
			'#prefix' => '<b>Рабочая программа</b><br>',
			'#markup' => $file_name . '<a href = "http://docs.google.com/viewer?url=' . $url . '" target="_blank"><img src = "/sites/all/pic/preview.png" title = "Просмотр"></a>',
			);
	}
		
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
		'#attributes' => array(
            $readonly => array($readonly),),
		'#default_value' => $discipline['Exam'],
		);

	$form['hour_and_control']['row']['CreditW/OGrade'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'За',
		'#attributes' => array(
            $readonly => array($readonly),),
		'#default_value' => $discipline['CreditW/OGrade'],
		);

	$form['hour_and_control']['row']['CreditWithGrade'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'ЗаО',
		'#attributes' => array(
            $readonly => array($readonly),),
		'#default_value' => $discipline['CreditWithGrade'],
		);

	$form['hour_and_control']['row']['Lecture'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'Лек',
		'#attributes' => array(
            $readonly => array($readonly),),
		'#default_value' => $discipline['Lecture'],
		);

	$form['hour_and_control']['row']['Lab'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'Лаб',
		'#attributes' => array(
            $readonly => array($readonly),),
		'#default_value' => $discipline['Lab'],
		);

	$form['hour_and_control']['row']['Practice'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'Практ',
		'#attributes' => array(
            $readonly => array($readonly),),
		'#default_value' => $discipline['Practice'],
		);

	$form['hour_and_control']['row']['Solo'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'СРС',
		'#attributes' => array(
            $readonly => array($readonly),),
		'#default_value' => $discipline['Solo'],
		);

	$form['hour_and_control']['row']['CourseProject'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'КП',
		'#attributes' => array(
            $readonly => array($readonly),),
		'#default_value' => $discipline['CourseProject'],
		);

	$form['hour_and_control']['row']['CourseWork'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'КР',
		'#attributes' => array(
            $readonly => array($readonly),),
		'#default_value' => $discipline['CourseWork'],
		);

	$form['hour_and_control']['row']['Zet'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'ЗЕТ',
		'#attributes' => array(
            $readonly => array($readonly),),
		'#default_value' => $discipline['Zet'],
		);

	$form['hour_and_control']['row']['Total'] = array(
		'#prefix' => '<td>',
		'#suffix' => '</td>',
		'#type' => 'textfield',
		'#size' => 5,
		'#title' => 'Всего',
		'#attributes' => array(
			'readonly' => array('readonly'),),
		'#default_value' => $discipline['Total'],
		);

	if(!$is_denied)
	{
		$form['save'] = array(
		'#type' => 'submit',
		'#value' => 'Сохранить',
		);
	}
	

	$form['back'] = array(
		'#type' => 'submit',
		'#value' => 'Назад',
		);
		

	return $form;
}

function disciplines_plan_view_discipline_form_submit($form, &$form_state)
{
	$dis_id = $_GET['id'];
	if(isset($form['back']['#value']) && $form_state['triggering_element']['#value'] == $form['back']['#value'])
	{
		$server = 'localhost';
		$username = 'moevm_user';
		$password = 'Pwt258E6JT8QAz3y';
		$database = 'moevmdb';

		$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
		mysqli_query ($mysqli, "SET NAMES `utf8`");

		$curriculum_result = mysqli_query ($mysqli, "SELECT `Curriculum` FROM `curriculumdiscipline`
			WHERE `idCurriculumDiscipline` = '" . $dis_id . "'");

		$curriculum = $curriculum_result->fetch_assoc();
		$curriculum_result->close();
		$mysqli->close();

		drupal_goto('disciplines/plan/view', array(
			'query' => array('id'=>$curriculum['Curriculum'],)));
	}

	if(isset($form['save']['#value']) && $form_state['triggering_element']['#value'] == $form['save']['#value'])
	{
		$data = array(
			'ID' => $dis_id,
			'Index' => $form_state['values']['Index'],
			'Name' => $form_state['values']['DisciplineName'],
			'Sem' => $form_state['values']['Semester'],
			'CurriculumNum' => $form_state['complete form']['CurriculumNum']['#options'][$form_state['values']['CurriculumNum']],
			'Chair' => $form_state['complete form']['ChairShortName']['#options'][$form_state['values']['ChairShortName']],
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

		save_update_curriculum_discipline($data);
	}
}

function get_curriculum_discipline_by_id($dis_id)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	$curriculum_discipline = array();

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$curriculum_discipline_result = mysqli_query ($mysqli, "SELECT * FROM `curriculumdiscipline`
		WHERE `idCurriculumDiscipline` = '" . $dis_id . "'");

	$curriculum_discipline = $curriculum_discipline_result->fetch_assoc();
	$curriculum_discipline_result->close();

	$discipline_result = mysqli_query ($mysqli, "SELECT `DisFullName`, `Chair` FROM `discipline`
		WHERE `idDiscipline` = '" . $curriculum_discipline['Discipline'] . "'");

	$discipline = $discipline_result->fetch_assoc();
	$discipline_result->close();

	$curriculum_result = mysqli_query ($mysqli, "SELECT `CurriculumNum` FROM `curriculum`
		WHERE `idCurriculum` = '" . $curriculum_discipline['Curriculum'] . "'");

	$curriculum = $curriculum_result->fetch_assoc();
	$curriculum_result->close();

	$chair_result = mysqli_query ($mysqli, "SELECT `ChairFullName` FROM `chair`
		WHERE `idChair` = '" . $discipline['Chair'] . "'");

	$chair = $chair_result->fetch_assoc();
	$chair_result->close();
	$mysqli->close();

	$curriculum_discipline['DisciplineName'] = $discipline['DisFullName'];
	$curriculum_discipline['CurriculumNum'] = $curriculum['CurriculumNum'];
	$curriculum_discipline['ChairFullName'] = $chair['ChairFullName'];

	return $curriculum_discipline;
}

function get_chairs()
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	$chairs = array();

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$chair_result = mysqli_query ($mysqli, "SELECT `ChairFullName` FROM `chair`");

	foreach ($chair_result as $row) 
	{
		$chairs[] = $row['ChairFullName'];
	}

	$chair_result->close();
	$mysqli->close();

	return $chairs;
}

function save_update_curriculum_discipline($data)
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

	$curriculum_result = mysqli_query ($mysqli, "SELECT `idCurriculum` FROM `curriculum`
		WHERE `CurriculumNum` = '" . $data['CurriculumNum'] . "'");

	$curriculum = $curriculum_result->fetch_assoc();
	$curriculum_result->close();

	$chair_result = mysqli_query ($mysqli, "SELECT `idChair` FROM `chair`
		WHERE `ChairFullName` = '" . $data['Chair'] . "'");

	$chair = $chair_result->fetch_assoc();
	$chair_result->close();

	$is_success_chair = mysqli_query ($mysqli, "UPDATE `discipline` SET
		`Chair` = '" . $chair['idChair'] . "'
		 WHERE `idDiscipline` = '" . $discipline['idDiscipline'] ."'");

	$total = intval($data['Lec']) + intval($data['Lab']) + intval($data['Pract']) + intval($data['Solo']);
	$is_success = mysqli_query ($mysqli, "UPDATE `curriculumdiscipline` SET
		`DisIndex` = '" . $data['Index'] . "',
		`Discipline` = '" . $discipline['idDiscipline'] . "',
		`Curriculum` = '" . $curriculum['idCurriculum'] . "',
		`Exam` = '" . $data['Exam'] . "',
		`CreditW/OGrade` = '" . $data['Za'] . "',
		`CreditWithGrade` = '" . $data['ZaO'] . "',
		`Lecture` = '" . $data['Lec'] . "',
		`Lab` = '" . $data['Lab'] . "',
		`Practice` = '" . $data['Pract'] . "',
		`Solo` = '" . $data['Solo'] . "',
		`CourseProject` = '" . $data['CP'] . "',
		`CourseWork` = '" . $data['CW'] . "',
		`Zet` = '" . $data['Zet'] . "',
		`Total` = '" . $total . "',
		`Semester` = '" . $data['Sem'] . "'
		 WHERE `idCurriculumDiscipline` = '" . $data['ID'] . "'");
		
	if($is_success && $is_success_chair)
		drupal_set_message('Данные сохранены успешно!');
	else
		drupal_set_message('Произошла ошибка при сохранении данных','error');

}

function get_work_program_path($dis_id)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$work_program_result = mysqli_query ($mysqli, "SELECT `FileName` FROM `workprogramversion`
		WHERE (`CurriculumDiscipline` = '" . $dis_id . "'
		AND `CurrentVersion` = 1)");

	foreach ($work_program_result as $row) 
	{
		$path = $row['FileName'];
	}

	if(isset($path))
		return $path;
	else
		return '';
}