<?php

function groups_other_view_form($form, &$form_state)
{	
	$group_id = $_GET['id'];

	$group = get_other_group_by_id($group_id);

	global $user;
    $is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);
    $is_denied = ($is_teacher || $is_student) && !$is_educational;

    if($is_denied)
	{
		$readonly = 'readonly';
	}
	else 
	{
		$readonly = '';
	}

	$form['group_num'] = array(
		'#type' => 'textfield',
		'#title' => 'Номер группы',
		'#maxlength' => 4, 
		'#attributes' => array(
            $readonly => array($readonly),),
		'#default_value' => $group['GroupNum'],
		'#size' => 50,
		);

	$form['size'] = array(
		'#type' => 'textfield',
		'#title' => 'Численность',
		'#default_value' => $group['Size'],
		'#maxlength' => 2, 
		'#attributes' => array(
            $readonly => array($readonly),),
		'#size' => 50,
		);

	$form['year'] = array(
		'#type' => 'textfield',
		'#title' => 'Год создания',
		'#default_value' => $group['CreationYear'],
		'#maxlength' => 4, 
		'#attributes' => array(
            $readonly => array($readonly),),
		'#size' => 50,
		);

	$form['email'] = array(
		'#type' => 'textfield',
		'#title' => 'E-mail',
		'#default_value' => $group['E-mail'],
		'#size' => 50,
		'#attributes' => array(
            $readonly => array($readonly),),
		);

	$form['curriculum'] = array(
		'#type' => 'textfield',
		'#title' => 'Номер УП',
		'#default_value' => $group['CurriculumNum'],
		'#maxlength' => 4, 
		'#attributes' => array(
            $readonly => array($readonly),),
		'#size' => 50,
		);

	$form['direction_code'] = array(
		'#type' => 'textfield',
		'#title' => 'Код направления',
		'#default_value' => $group['DirectionCode'], 
		'#size' => 50,
		'#attributes' => array(
            $readonly => array($readonly),),
		);

	$form['direction_name'] = array(
		'#type' => 'textfield',
		'#title' => 'Название направления', 
		'#default_value' => $group['DirectionName'],
		'#size' => 50,
		'#attributes' => array(
            $readonly => array($readonly),),
		);

	$form['chair'] = array(
		'#type' => 'textfield',
		'#title' => 'Кафедра',
		'#default_value' => $group['ChairFullName'], 
		'#size' => 50,
		'#attributes' => array(
            $readonly => array($readonly),),
		);

	if(!$is_denied)
	{
		$form['chair_num'] = array(
			'#type' => 'textfield',
			'#title' => 'Номер кафедры', 
			'#default_value' => $group['ChairNum'],
			'#size' => 50,
			'#attributes' => array(
	            $readonly => array($readonly),),
			);

		$form['add_group'] = array(
			'#type' => 'submit',
			'#value' => 'Сохранить',
			);

	}

	return $form;
}

function groups_other_view_form_submit($form, &$form_state)
{	
	$group_id = $_GET['id'];
	$server = 'localhost';
    $username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $group_result = mysqli_query ($mysqli, "SELECT `Curriculum` FROM `group`
    	WHERE `idGroup` = '" . $group_id . "'");

    $group = $group_result->fetch_assoc();
	$group_result->close();

	$curriculum_result = mysqli_query ($mysqli, "SELECT `Direction`, `Chair` FROM `curriculum`
    	WHERE `idCurriculum` = '" . $group['Curriculum'] . "'");

	$curriculum = $curriculum_result->fetch_assoc();
	$curriculum_result->close();

    if(!empty($form_state['values']['chair_num']))
    {
    	mysqli_query ($mysqli, "UPDATE `chair` SET
			`ChairNum` = '" . $form_state['values']['chair_num'] . "',
			`ChairFullName` = '" . $form_state['values']['chair'] . "'
			WHERE `idChair` = '" . $curriculum['Chair'] . "'
			");
    }
    else
    {
    	mysqli_query ($mysqli, "UPDATE `chair` SET
			`ChairFullName` = '" . $form_state['values']['chair'] . "'
			WHERE `idChair` = '" . $curriculum['Chair'] . "'
			");
    }

    mysqli_query ($mysqli, "UPDATE `direction` SET
		`DirectionCode` = '" . $form_state['values']['direction_code'] . "',
		`DirectionName` = '" . $form_state['values']['direction_name'] . "'
		WHERE `idDirection` = '" . $curriculum['Direction'] . "'");

    mysqli_query ($mysqli, "UPDATE `group` SET 
		`GroupNum` = '" . $form_state['values']['group_num'] . "',
		`Size` = '" . $form_state['values']['size'] . "',
		`CreationYear` = '" . $form_state['values']['year'] . "',
		`E-mail` = '" . $form_state['values']['email'] . "'
		WHERE `idGroup` = '" . $group_id . "'
		");

    
    drupal_set_message('Данные обновлены успешно!');
   
}

function get_other_group_by_id($group_id)
{
	$server = 'localhost';
    $username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $group_result = mysqli_query ($mysqli, "SELECT *
    	FROM `group`
    	WHERE `idGroup` = '" . $group_id . "'");

    $group = $group_result->fetch_assoc();
    $group_result->close();

    $curriculum_result = mysqli_query ($mysqli, "SELECT `CurriculumNum`, `Chair`, `Direction` 
    	FROM `curriculum`
    	WHERE `idCurriculum` = '" . $group['Curriculum'] . "'");

    $curriculum = $curriculum_result->fetch_assoc();
    $curriculum_result->close();

    $chair_result =  mysqli_query ($mysqli, "SELECT `ChairNum`, `ChairFullName` FROM `chair`
		WHERE `idChair` = '" . $curriculum['Chair'] . "'");

    $chair = $chair_result->fetch_assoc();
    $chair_result->close();

    $direction_result =  mysqli_query ($mysqli, "SELECT `DirectionCode`, `DirectionName` FROM `direction`
		WHERE `idDirection` = '" . $curriculum['Direction'] . "'");

    $direction = $direction_result->fetch_assoc();
    $direction_result->close();

    $mysqli->close();

    $group['CurriculumNum'] = $curriculum['CurriculumNum'];
    $group['ChairFullName'] = $chair['ChairFullName'];
    $group['ChairNum'] = $chair['ChairNum'];
    $group['DirectionCode'] = $direction['DirectionCode'];
    $group['DirectionName'] = $direction['DirectionName'];

	return $group;
}