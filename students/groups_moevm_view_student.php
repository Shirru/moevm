<?php

function groups_moevm_view_student_form($form, &$form_state)
{
	global $user;
    $is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);
    $is_denied = ($is_teacher || $is_student) && !$is_educational;

	$student_id = $_GET['id'];
	$student = get_student_by_id($student_id);
	$groups_options = get_groups_options();

	for($i = 0; $i < count($groups_options); $i++) 
	{
		if($groups_options[$i] == $student['GroupNum'])
			$default_group = $i;
	}


	if($is_denied)
    {
        $readonly = 'readonly'; 
    }
    else
    {
        $readonly = '';
    }
		
    if(!$is_denied)
	{
		$form['Passport'] = array(
	        '#type' => 'fieldset', 
	        '#collapsible' => TRUE, 
			'#collapsed' => FALSE, 
	        '#title' => 'Паспортные данные', 
	        '#prefix' => '<div class="container-inline">',
	        '#suffix' => '</div>',
	    	);

	    $form['Passport']['Number'] = array(
	        '#type' => 'textfield', 
	        '#title' => 'Серия', 
	        '#maxlength' => 4,
	        '#size' => 4,
	        '#default_value' =>  substr($student['Passport'], 0, 4),
	        '#attributes' => array(
				'readonly' => array('readonly')),
	    	);

	    $form['Passport']['Series'] = array(
	        '#type' => 'textfield', 
	        '#title' => 'Номер', 
	        '#maxlength' => 6,
	        '#size' => 6,
	        '#default_value' =>  substr($student['Passport'], 4),
	        '#attributes' => array(
				'readonly' => array('readonly')),
	    	);
	}

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
		'#default_value' => $student['RecordBookNum'],
		'#attributes' => array(
            $readonly => array($readonly),),
		'#size' => 30,
		);

	$form['column_left']['surname'] = array(
		'#type' => 'textfield',
		'#title' => 'Фамилия',
		'#default_value' => $student['Surname'],
		'#attributes' => array(
            $readonly => array($readonly),),
		'#size' => 30,
		);

	$form['column_left']['first_name'] = array(
		'#type' => 'textfield',
		'#title' => 'Имя',
		'#default_value' => $student['FirstName'],
		'#attributes' => array(
            $readonly => array($readonly),),
		'#size' => 30,
		);

	$form['column_left']['patronymic'] = array(
		'#type' => 'textfield',
		'#title' => 'Отчество',
		'#default_value' => $student['Patronymic'],
		'#attributes' => array(
            $readonly => array($readonly),),
		'#size' => 30,
		);

 	if(!$is_denied)
	{
		if(empty($student['E-mail']))
		{
			$form['column_left']['email'] = array(
				'#type' => 'textfield',
				'#title' => 'E-mail',
				'#attributes' => array(
	            	$readonly => array($readonly),),	
				'#default_value' => $student['E-mail'],
				'#size' => 30,
				);
		}
		else
		{
			$form['column_left']['email'] = array(
				'#type' => 'textfield',
				'#title' => 'E-mail',
				'#default_value' => $student['E-mail'],
				'#size' => 30,
				'#attributes' => array(
					'readonly' => array('readonly')),
				);
		}

	}
	
	$form['column_right'] = array(
		'#type' => 'container',
		'#attributes' => array(
			'class' => array('column-right'),
			'style' => array('float: right'),
			),
		);

	$form['column_right']['group'] = array(
		'#type' => 'select',
		'#title' => 'Номер группы',
		'#attributes' => array(
            $readonly => array($readonly),),
		'#options' => get_groups_options(),
		'#default_value' => $default_group,
		);


	if(!$is_denied)
	{
		$form['column_right']['address'] = array(
		'#type' => 'textfield',
		'#title' => 'Адрес',
		'#default_value' => $student['Address'],
		'#size' => 30,
		'#attributes' => array(
			'readonly' => array('readonly')),
		);

		$form['column_right']['phone'] = array(
			'#type' => 'textfield',
			'#title' => 'Телефон',
			'#default_value' => $student['Phone'],
			'#size' => 30,
			'#attributes' => array(
				'readonly' => array('readonly')),
			);

		$form['column_right']['enrollment_date'] = array(
			'#type' => 'date',
			'#title' => 'Дата зачисления',
			'#attributes' => array(
	            $readonly => array($readonly),),
			'#default_value' => array('year' => intval(substr($student['EnrollmentDate'], 0, 4)),
				 'month' => intval(substr($student['EnrollmentDate'], 5, 2)),
				  'day' => intval(substr($student['EnrollmentDate'], 8, 2))), 
			);

		$form['column_right']['expel_date'] = array(
			'#type' => 'date',
			'#title' => 'Дата отчисления',
			'#attributes' => array(
	            $readonly => array($readonly),),
			'#default_value' => array('year' => intval(substr($student['ExpelDate'], 0, 4)),
				 'month' => intval(substr($student['ExpelDate'], 5, 2)),
				  'day' => intval(substr($student['ExpelDate'], 8, 2))), 
			);

		$form['column_left']['submit'] = array(
			'#type' => 'submit',
			'#value' => 'Сохранить',
			);

	}

	return $form;
}

function groups_moevm_view_student_form_submit($form, &$form_state)
{
	$student_id = $_GET['id'];
	$server = 'localhost';
    $username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $group_result =  mysqli_query ($mysqli, "SELECT `idGroup` FROM `group` 
    	WHERE `GroupNum` = '" . $form_state['complete form']['column_right']['group']['#options'][$form_state['values']['group']] . "'");

    $group = $group_result->fetch_assoc();
    $group_result->close();

    $is_success = mysqli_query ($mysqli, "UPDATE `student` SET
    	`Surname` = '" . $form_state['values']['surname'] . "',
    	`FirstName` = '" . $form_state['values']['first_name'] . "',
    	`Patronymic` = '" . $form_state['values']['patronymic'] . "',
    	`Group` = '" . $group['idGroup'] . "',
    	`RecordBookNum` = '" . $form_state['values']['credit_book'] . "',
    	`E-mail` = '" . $form_state['values']['email'] . "',
    	`EnrollmentDate` = '" . $form_state['values']['enrollment_date']['year'] . '-' . $form_state['values']['enrollment_date']['month'] . '-' . $form_state['values']['enrollment_date']['day'] . "',
    	`ExpelDate` = '" . $form_state['values']['expel_date']['year'] . '-' . $form_state['values']['expel_date']['month'] . '-' . $form_state['values']['expel_date']['day'] . "'
    	WHERE `idStudent` = '" . $student_id . "'");
    
    $mysqli->close();

    if($is_success)
    	drupal_set_message('Данные успешно обновлены!');
    else
    	drupal_set_message('Произошла ошибка при сохранении данных', 'error');

    drupal_goto('groups/moevm/view', array(
			'query' => array('id'=>$group['idGroup'],)));
}

function get_student_by_id($student_id)
{
	$server = 'localhost';
    $username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $student_result = mysqli_query ($mysqli, "SELECT * FROM `student`
    	WHERE `idStudent` = '" . $student_id . "'");

    $student = $student_result->fetch_assoc();
    $student_result->close();

    $group_result = mysqli_query ($mysqli, "SELECT `GroupNum` FROM `group`
    	WHERE `idGroup` = '" . $student['Group'] . "'");

    $group = $group_result->fetch_assoc();
    $group_result->close();
    $mysqli->close();

    $student['GroupNum'] = $group['GroupNum'];

	return $student;
}

function get_groups_options()
{
	$server = 'localhost';
    $username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $group_result = mysqli_query ($mysqli, "SELECT `GroupNum` FROM `group`");

    foreach ($group_result as $group) 
    {
    	$groups[] = $group['GroupNum'];
    }

	return $groups;
}