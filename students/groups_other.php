<?php

function groups_other_form($form, &$form_state)
{
	global $user;
    $is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);
    $is_denied = ($is_teacher || $is_student) && !$is_educational;

	$groups = groups_other_split_all();
	$tables = get_other_groups_tables($groups);

	if(!$is_denied)
		$header = array('', 'Группа', 'Численность', 'Год создания', 'E-mail', 'Учебный план', '');
	else
		$header = array('', 'Группа', 'Численность', 'Год создания', 'E-mail', 'Учебный план');

	for($i = 1; $i <= 7; $i++)
	{
		if(!empty($tables[$i]['rows']))
		{
			$form['table' . $i] = array(
				'#type' => 'fieldset',
				'#title' => $tables[$i]['fac'],
				'#collapsible' => true,
				);

			$form['table' . $i]['groups'] = array(
				'#markup' => theme('table', array('header' => $header, 'rows' => $tables[$i]['rows'])),
				);
		}
	
	}

	if(!$is_denied)
	{
		$form['add_group'] = array(
			'#type' => 'submit',
			'#value' => 'Добавить группу',
			);
	}

	return $form;
}

function groups_other_form_submit($form, &$form_state)
{
	drupal_goto('groups/other/add');
}

function groups_other_add_form($form, &$form_state)
{
	$form['group_num'] = array(
		'#type' => 'textfield',
		'#title' => 'Номер группы',
		'#maxlength' => 4, 
		'#size' => 50,
		);

	$form['size'] = array(
		'#type' => 'textfield',
		'#title' => 'Численность',
		'#maxlength' => 2, 
		'#size' => 50,
		);

	$form['year'] = array(
		'#type' => 'textfield',
		'#title' => 'Год создания',
		'#maxlength' => 4, 
		'#size' => 50,
		);

	$form['email'] = array(
		'#type' => 'textfield',
		'#title' => 'E-mail',
		'#size' => 50,
		);

	$form['curriculum'] = array(
		'#type' => 'textfield',
		'#title' => 'Номер УП',
		'#maxlength' => 4, 
		'#size' => 50,
		);

	$form['direction_code'] = array(
		'#type' => 'textfield',
		'#title' => 'Код направления', 
		'#size' => 50,
		);

	$form['direction_name'] = array(
		'#type' => 'textfield',
		'#title' => 'Название направления', 
		'#size' => 50,
		);

	$form['chair'] = array(
		'#type' => 'textfield',
		'#title' => 'Кафедра', 
		'#size' => 50,
		);

	$form['chair_num'] = array(
		'#type' => 'textfield',
		'#title' => 'Номер кафедры', 
		'#size' => 50,
		);

	$form['add_group'] = array(
		'#type' => 'submit',
		'#value' => 'Добавить группу',
		);
	return $form;
}

function groups_other_add_form_submit($form, &$form_state)
{
	$server = 'localhost';
    $username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $faculty_result = mysqli_query ($mysqli, "SELECT `idFaculty` FROM `faculty`
		WHERE `FacultyNum` = '" . mb_substr($form_state['values']['group_num'], 1, 1) . "'");

    $faculty = $faculty_result->fetch_assoc();
    $faculty_result->close();

    if(!empty($form_state['values']['chair']))
    {
    	mysqli_query ($mysqli, "INSERT INTO `chair`
			(`ChairNum`, `ChairFullName`)
			VALUES ('" . $form_state['values']['chair_num'] . "',
			'" . $form_state['values']['chair'] . "')");

	    $chair_result =  mysqli_query ($mysqli, "SELECT `idChair` FROM `chair`
			WHERE `ChairFullName` = '" . $form_state['values']['chair'] . "'");
	    $chair = $chair_result->fetch_assoc();
	    $chair_result->close();
    }
    else
    {
    	$chair['idChair'] = NULL;
    }

    mysqli_query ($mysqli, "INSERT INTO `direction`
		(`DirectionCode`, `DirectionName`, `Faculty`)
		VALUES ('" . $form_state['values']['direction_code'] . "',
		'" . $form_state['values']['direction_name'] . "',
		'" . $faculty['idFaculty'] . "')");

    $direction_result =  mysqli_query ($mysqli, "SELECT `idDirection` FROM `direction`
		WHERE `DirectionCode` = '" . $form_state['values']['direction_code'] . "'");

    $direction = $direction_result->fetch_assoc();
    $direction_result->close();


    mysqli_query ($mysqli, "INSERT INTO `curriculum`
		(`CurriculumNum`, `Direction`, `Chair`)
		VALUES ('" . $form_state['values']['curriculum'] . "',
		'" . $direction['idDirection'] . "',
		 '" . $chair['idChair'] . "')");


    $curriculum_result =  mysqli_query ($mysqli, "SELECT `idCurriculum` FROM `curriculum`
		WHERE `CurriculumNum` = '" . $form_state['values']['curriculum'] . "'");

    $curriculum = $curriculum_result->fetch_assoc();
    $curriculum_result->close();

    $is_success = mysqli_query ($mysqli, "INSERT INTO `group`
		(`GroupNum`, `Size`, `CreationYear`, `E-mail`, `Curriculum`)
		VALUES ('" . $form_state['values']['group_num'] . "',
		'" . $form_state['values']['size'] . "',
		 '" . $form_state['values']['year'] . "',
		 '" . $form_state['values']['email'] . "',
		 '" . $curriculum['idCurriculum'] . "')");

    if($is_success)
    	drupal_set_message('Данные добавлены успешно!');
    else
    	drupal_set_message('Произошла ошибка при сохранении данных', 'error');

    drupal_goto('groups/other');
}

function groups_other_split_all()
{
	$server = 'localhost';
    $username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
    $groups = array(
    	1 => NULL,
    	2 => NULL,
    	3 => NULL,
    	4 => NULL,
    	5 => NULL,
    	6 => NULL,
    	7 => NULL,
    	);
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $groups_result = mysqli_query ($mysqli, "SELECT * FROM `group`");

    foreach ($groups_result as $row) 
    {
    	$curriculum_result =  mysqli_query ($mysqli, "SELECT `CurriculumNum`, `Chair` FROM `curriculum`
		WHERE `idCurriculum` = '" . $row['Curriculum'] . "'");

	    $curriculum = $curriculum_result->fetch_assoc();
	    $curriculum_result->close();

	    $chair_result =  mysqli_query ($mysqli, "SELECT `ChairNum` FROM `chair`
		WHERE `idChair` = '" . $curriculum['Chair'] . "'");

	    $chair = $chair_result->fetch_assoc();
	    $chair_result->close();
	    $row['CurriculumNum'] = $curriculum['CurriculumNum'];

	    if($chair['ChairNum'] != 14 && !empty($chair))
	    {
	    	switch (mb_substr($row['GroupNum'], 1, 1)) {
	    		case '1':
	    			$groups['1'][] = $row;
	    			break;
	    		case '2':
	    			$groups['2'][] = $row;
	    			break;
	    		case '3':
	    			$groups['3'][] = $row;
	    			break;
	    		case '4':
	    			$groups['4'][] = $row;
	    			break;
	    		case '5':
	    			$groups['5'][] = $row;
	    			break;
	    		case '6':
	    			$groups['6'][] = $row;
	    			break;
	    		case '7':
	    			$groups['7'][] = $row;
	    			break;
	    	}
	    }
    }
    $groups_result->close();
    $mysqli->close();

    return $groups;
}

function get_other_groups_tables($groups)
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
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");
	$tables = array();

	for($i = 1; $i <= 7; $i++)
	{
		$rows = array();
		$faculty_result = mysqli_query ($mysqli, "SELECT `FacultyShortName` FROM `faculty`
			WHERE `FacultyNum` = '" . $i . "'");

		$faculty = $faculty_result->fetch_assoc();
	    $faculty_result->close();

	    $tables[$i]['fac'] = $faculty['FacultyShortName'];

	    if(!$is_denied)
	    {
	    	for($j = 0; $j < count($groups[$i]); $j++)
		    {
		    	$rows[] = array(
		    		"<a href='other/view?id=" . $groups[$i][$j]['idGroup'] . "'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
		    		$groups[$i][$j]['GroupNum'],
		    		$groups[$i][$j]['Size'],
		    		$groups[$i][$j]['CreationYear'],
		    		$groups[$i][$j]['E-mail'],
		    		$groups[$i][$j]['CurriculumNum'],
		    		"<a href='#' onclick='if(confirm(\"Вы действительно хотите удалить группу?\")){parent.location = \"/groups/del?other_group_id=" . $groups[$i][$j]['idGroup'] . "\";}else return false;'  title='удаление'><img src='/sites/all/pic/delete.png'></a>"
		    	);
		    }
	    }
	    else
	    {
	    	for($j = 0; $j < count($groups[$i]); $j++)
		    {
		    	$rows[] = array(
		    		"<a href='other/view?id=" . $groups[$i][$j]['idGroup'] . "'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
		    		$groups[$i][$j]['GroupNum'],
		    		$groups[$i][$j]['Size'],
		    		$groups[$i][$j]['CreationYear'],
		    		$groups[$i][$j]['E-mail'],
		    		$groups[$i][$j]['CurriculumNum'],
		    	);
		    }
	    }
	 
	   
	    $tables[$i]['rows'] = $rows;
	}
	return $tables;
}