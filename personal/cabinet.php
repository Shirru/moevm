<?php
include 'work_program.php';
include 'individual_plan.php';

function personal_page()
{
 	global $user;
 	if($is_teacher = array_search('teacher', $user->roles))
 	{
 		return personal_teacher_page();
 	}
 	else if($is_student = array_search('student', $user->roles))
 	{
		return personal_student_page();
 	}
 	else
 	{
 		return "";
 	}

}

function personal_teacher_page()
{
	$output = "";
	$personal_data_form = drupal_get_form('personal_teacher_data_form');
	$output .= render($personal_data_form);
	$output .= personal_teacher_individual_plan();
	$personal_teacher_work_program_form = drupal_get_form('personal_teacher_work_program_form');
	$output .= render($personal_teacher_work_program_form);
	return $output;
}

function personal_teacher_data_form($form, &$form_state)
{
	global $user;
	$email = $user->mail;
	$ranks = array('Не выбрано', 'доцент', 'профессор');
	$default_rank = 0;

	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $teacher_result = $mysqli->query("SELECT *
                 FROM teacher
                 WHERE `E-Mail` = '" . $email . "'");
    $mysqli->close();
	$teacher = $teacher_result->fetch_assoc();
	$teacher_result->close();

	for ($i = 0; $i < count($ranks); $i++) 
	{ 
		if(mb_stripos($teacher['Rank'], $ranks[$i]) !== false)
			$default_rank = $i;
	}


	$form_state['storage']['teacher_id'] = $teacher['idTeacher'];

    // Личные данные
    $form['personal_data'] = array(
        '#type' => 'fieldset',
        '#collapsible' => TRUE, 
			'#collapsed' => TRUE, 
        '#title' => 'Личные данные',
      	);

     $form['personal_data']['Passport'] = array(
        '#type' => 'fieldset', 
        '#collapsible' => TRUE, 
		'#collapsed' => FALSE, 
        '#title' => 'Паспортные данные', 
        '#prefix' => '<div class="container-inline">',
        '#suffix' => '</div>',
    	);

     $form['personal_data']['Passport']['Number'] = array(
        '#type' => 'textfield', 
        '#title' => 'Серия', 
        '#maxlength' => 4,
        '#size' => 4,
        '#default_value' =>  substr($teacher['Passport'], 0, 4),
    	);

     $form['personal_data']['Passport']['Series'] = array(
        '#type' => 'textfield', 
        '#title' => 'Номер', 
        '#maxlength' => 6,
        '#size' => 6,
        '#default_value' =>  substr($teacher['Passport'], 4),
    	);

    $form['personal_data']['column_left'] = array(
		'#type' => 'container',
		'#attributes' => array(
			'class' => array('column-left'),
			'style' => array('float: left'),
			),
		);

    $form['personal_data']['column_left']['Surname'] = array(
        '#type' => 'textfield', 
			'#title' => t('Фамилия'), 
			'#size' => 30,
			'#default_value' => $teacher['Surname'], 
    	);

    $form['personal_data']['column_left']['FirstName'] = array(
        '#type' => 'textfield', 
			'#title' => t('Имя'), 
			'#size' => 30,
			'#default_value' => $teacher['FirstName'], 
    	);

    $form['personal_data']['column_left']['Patronymic'] = array(
        '#type' => 'textfield', 
			'#title' => t('Отчество'), 
			'#size' => 30,
			'#default_value' => $teacher['Patronymic'], 
    	);

    $form['personal_data']['column_left']['HomePhone'] = array(
        '#type' => 'textfield', 
			'#title' => t('Домашний телефон'), 
			'#size' => 30,
			'#default_value' => $teacher['HomePhone'], 
    	);

    $form['personal_data']['column_left']['Mobile'] = array(
        '#type' => 'textfield', 
			'#title' => t('Мобильный телефон'),
			'#size' => 30, 
			'#default_value' => $teacher['Mobile'], 
			//'#field_prefix' => t('+7'),
    	);

    $form['personal_data']['column_left']['WorkPhone'] = array(
        '#type' => 'textfield', 
			'#title' => t('Рабочий телефон'),
			'#size' => 30, 
			'#default_value' => $teacher['WorkPhone'], 
    	);

    $form['personal_data']['column_left']['Email'] = array(
        '#type' => 'textfield', 
			'#title' => t('E-mail'), 
			'#size' => 30,
			'#default_value' => $teacher['E-mail'], 
    	);

    $form['personal_data']['column_left']['Address'] = array(
        '#type' => 'textfield', 
			'#title' => t('Адрес'), 
			'#size' => 30,
			'#default_value' => $teacher['Address'], 
    	);

    $form['personal_data']['column_left']['BirthDate'] = array(
        '#type' => 'date', 
			'#title' => t('Дата рождения'), 
			'#default_value' => array('year' => intval(substr($teacher['BirthDate'], 0, 4)),
			 'month' => intval(substr($teacher['BirthDate'], 5, 2)),
			  'day' => intval(substr($teacher['BirthDate'], 8, 2))), 
    	);

    $form['personal_data']['column_right'] = array(
		'#type' => 'container',
		'#attributes' => array(
			'class' => array('column-right'),
			'style' => array('float: right'),
			),
		);

    $form['personal_data']['column_right']['Position'] = array(
		'#type' => 'textfield', 
			'#title' => t('Должность'), 
			'#size' => 30,
			'#default_value' => $teacher['Position'], 
			'#attributes' => array(
			'readonly' => array('readonly'),
			'style' => array('border: 0px;'),
			),
    	);

    $form['personal_data']['column_right']['ShareRates'] = array(
		'#type' => 'textfield', 
			'#title' => t('Доля ставки'), 
			'#size' => 30,
			'#default_value' => $teacher['ShareRates'], 
			'#attributes' => array(
			'readonly' => array('readonly'),
			'style' => array('border: 0px;'),
			),
    	);

    $form['personal_data']['column_right']['Degree'] = array(
		'#type' => 'textfield', 
			'#title' => t('Степень'), 
			'#size' => 30,
			'#default_value' => $teacher['Degree'], 
			'#attributes' => array(
			'readonly' => array('readonly'),
			'style' => array('border: 0px;'),
			),
    	);

    $form['personal_data']['column_right']['Rank'] = array(
		'#type' => 'select', 
			'#title' => t('Звание'), 
			'#options' => $ranks,
			'#default_value' => $default_rank,
    	);

    $form['personal_data']['column_right']['Contract'] = array(
		'#type' => 'textfield', 
			'#title' => t('Вид договора'), 
			'#size' => 30,
			'#default_value' => $teacher['Contract'], 
			'#attributes' => array(
			'readonly' => array('readonly'),
			'style' => array('border: 0px;'),
			),

    	);

    $form['personal_data']['column_right']['ConclusionDate'] = array(
		'#type' => 'date', 
			'#title' => t('Дата заключения договора'), 
			'#default_value' => array('year' => intval(substr($teacher['ConclusionDate'], 0, 4)),
			 'month' => intval(substr($teacher['ConclusionDate'], 5, 2)),
			  'day' => intval(substr($teacher['ConclusionDate'], 8, 2))), 
    	);

    $form['personal_data']['column_right']['TerminationDate'] = array(
		'#type' => 'date', 
			'#title' => t('Дата окончания договора'), 
			'#default_value' => array('year' => intval(substr($teacher['TerminationDate'], 0, 4)),
			 'month' => intval(substr($teacher['TerminationDate'], 5, 2)),
			  'day' => intval(substr($teacher['TerminationDate'], 8, 2))), 

    	);

    $effective_contract = $teacher["EffectiveContract"] ? 1 : 0; //Да : Нет

    $form['personal_data']['column_right']['EffectiveContract'] = array(
		'#type' => 'select', 
			'#title' => t('Эффективный контракт'), 
			'#options' => array ("Нет", "Да"),
			'#default_value' => $effective_contract, 
    	);

    $form['personal_data']['column_left']['submit'] = array(
    	'#type' => 'submit',
    	'#value' => t('Сохранить'),
    	);

    return $form;
}

function personal_teacher_data_form_submit($form, &$form_state)
{
	global $user;
//	debug($form_state['values']['Number']);

	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	
	$rank = $form_state['values']['Rank'] == 0 ? '' : $form['personal_data']['column_right']['Rank']['#options'][$form_state['values']['Rank']];
	$effective_contract = $form_state['values']['EffectiveContract'] == 0 ? 0 : 1;

  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

	if($form_state['values']['Number'] != "" || $form_state['values']['Series'] != "")
	{
		 mysqli_query($mysqli, "UPDATE teacher
    						SET `Passport` = '" . $form_state['values']['Number'] . $form_state['values']['Series'] . "',
    						`Surname` = '" . $form_state['values']['Surname'] . "', 
    						`FirstName` = '" . $form_state['values']['FirstName'] . "',
    						`Patronymic` = '" . $form_state['values']['Patronymic'] . "',
    						`HomePhone` = '" . $form_state['values']['HomePhone'] . "',
    						`Mobile` = '" . $form_state['values']['Mobile'] . "',
    						`WorkPhone` = '" . $form_state['values']['WorkPhone'] . "',
    						`E-mail` = '" . $form_state['values']['Email'] . "',
    						`Address` = '" . $form_state['values']['Address'] . "',
    						`BirthDate` = '" . $form_state['values']['BirthDate']['year'] . '-' . $form_state['values']['BirthDate']['month'] . '-' . $form_state['values']['BirthDate']['day'] . "',
						`Rank` = '" . $rank . "',
						`ConclusionDate` = '" . $form_state['values']['ConclusionDate']['year'] . '-' . $form_state['values']['ConclusionDate']['month'] . '-' . $form_state['values']['ConclusionDate']['day'] . "',
						`TerminationDate` = '" . $form_state['values']['TerminationDate']['year'] . '-' . $form_state['values']['TerminationDate']['month'] . '-' . $form_state['values']['TerminationDate']['day'] . "',
						`EffectiveContract` = '" . $effective_contract . "'
    						WHERE `idTeacher` = '" . $form_state['storage']['teacher_id'] . "'
    						");
	}
	else
	{
		 mysqli_query($mysqli, "UPDATE teacher
    						SET `Surname` = '" . $form_state['values']['Surname'] . "', 
    						`FirstName` = '" . $form_state['values']['FirstName'] . "',
    						`Patronymic` = '" . $form_state['values']['Patronymic'] . "',
    						`HomePhone` = '" . $form_state['values']['HomePhone'] . "',
    						`Mobile` = '" . $form_state['values']['Mobile'] . "',
    						`WorkPhone` = '" . $form_state['values']['WorkPhone'] . "',
    						`E-mail` = '" . $form_state['values']['Email'] . "',
    						`Address` = '" . $form_state['values']['Address'] . "',
    						`BirthDate` = '" . $form_state['values']['BirthDate']['year'] . '-' . $form_state['values']['BirthDate']['month'] . '-' . $form_state['values']['BirthDate']['day'] . "',
						`Rank` = '" . $rank . "',
						`ConclusionDate` = '" . $form_state['values']['ConclusionDate']['year'] . '-' . $form_state['values']['ConclusionDate']['month'] . '-' . $form_state['values']['ConclusionDate']['day'] . "',
						`TerminationDate` = '" . $form_state['values']['TerminationDate']['year'] . '-' . $form_state['values']['TerminationDate']['month'] . '-' . $form_state['values']['TerminationDate']['day'] . "',
						`EffectiveContract` = '" . $effective_contract . "'
    						WHERE `idTeacher` = '" . $form_state['storage']['teacher_id'] . "'
    						");
	}

   
    $mysqli->close();

    $c_user=user_load($user->uid);
	$c_user->mail = $form_state['values']['Email'];
	user_save($c_user);

	drupal_set_message("Данные успешно изменены!");
}

function personal_student_page()
{
	$output = "";
	$personal_data_form = drupal_get_form('personal_student_data_form');
	$output .= render($personal_data_form);
	return $output;
}

function personal_student_data_form($form, &$form_state)
{
	global $user;
	$email = $user->mail;

	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $student_result = $mysqli->query("SELECT *
                 FROM student
                 WHERE `E-Mail` = '" . $email . "'");

	$student = $student_result->fetch_assoc();
	$student_result->close();

	$group_result = $mysqli->query("SELECT `GroupNum`
									FROM `group`
									WHERE `idGroup` = '" . $student['Group'] . "'");

	$group = $group_result->fetch_assoc();
	$group_result->close();

    $mysqli->close();

	$form_state['storage']['student_id'] = $student['idStudent'];

    // Личные данные
    $form['personal_data'] = array(
        '#type' => 'fieldset',
        '#collapsible' => TRUE, 
		'#collapsed' => FALSE, 
        '#title' => 'Личные данные',
      	);

     $form['personal_data']['Passport'] = array(
        '#type' => 'fieldset', 
        '#title' => 'Паспортные данные',
        '#collapsible' => TRUE, 
		'#collapsed' => FALSE,  
        '#prefix' => '<div class="container-inline">',
        '#suffix' => '</div>',
    	);

     $form['personal_data']['Passport']['Number'] = array(
        '#type' => 'textfield', 
        '#title' => 'Серия', 
        '#maxlength' => 4,
        '#size' => 4,
        '#default_value' =>  substr($student['Passport'], 0, 4),
    	);

     $form['personal_data']['Passport']['Series'] = array(
        '#type' => 'textfield', 
        '#title' => 'Номер', 
        '#maxlength' => 6,
        '#size' => 6,
        '#default_value' =>  substr($student['Passport'], 4),
    	);

    $form['personal_data']['column_left'] = array(
		'#type' => 'container',
		'#attributes' => array(
			'class' => array('column-left'),
			'style' => array('float: left'),
			),
		);

    $form['personal_data']['column_left']['Surname'] = array(
        '#type' => 'textfield', 
			'#title' => t('Фамилия'), 
			'#size' => 30,
			'#default_value' => $student['Surname'], 
    	);

    $form['personal_data']['column_left']['FirstName'] = array(
        '#type' => 'textfield', 
			'#title' => t('Имя'), 
			'#size' => 30,
			'#default_value' => $student['FirstName'], 
    	);

    $form['personal_data']['column_left']['Patronymic'] = array(
        '#type' => 'textfield', 
			'#title' => t('Отчество'), 
			'#size' => 30,
			'#default_value' => $student['Patronymic'], 
    	);

    $form['personal_data']['column_left']['Email'] = array(
        '#type' => 'textfield', 
			'#title' => t('E-mail'), 
			'#size' => 30,
			'#default_value' => $student['E-mail'], 
    	);

    $form['personal_data']['column_left']['Address'] = array(
        '#type' => 'textfield', 
			'#title' => t('Адрес'), 
			'#size' => 30,
			'#default_value' => $student['Address'], 
    	);
   
    $form['personal_data']['column_left']['Phone'] = array(
        '#type' => 'textfield', 
			'#title' => t('Мобильный телефон'),
			'#size' => 30, 
			'#default_value' => $student['Phone'], 
			//'#field_prefix' => t('+7'),
    	);

    $form['personal_data']['column_right'] = array(
		'#type' => 'container',
		'#attributes' => array(
			'class' => array('column-right'),
			'style' => array('float: right'),
			),
		);

    $form['personal_data']['column_right']['RecordBookNum'] = array(
		'#type' => 'textfield', 
			'#title' => t('Номер зачетной книжки'), 
			'#size' => 30,
			'#default_value' => $student['RecordBookNum'], 
			'#attributes' => array(
			'readonly' => array('readonly'),
			'style' => array('border: 0px;'),
			),
    	);

    $form['personal_data']['column_right']['Group'] = array(
		'#type' => 'textfield', 
			'#title' => t('Номер группы'), 
			'#size' => 30,
			'#default_value' => $group['GroupNum'], 
			'#attributes' => array(
			'readonly' => array('readonly'),
			'style' => array('border: 0px;'),
			),
    	);

    $form['personal_data']['column_right']['EnrollmentDate'] = array(
		'#type' => 'textfield', 
			'#title' => t('Дата зачисления'), 
			'#size' => 30,
			'#default_value' => $student['EnrollmentDate'], 
			'#attributes' => array(
			'readonly' => array('readonly'),
			'style' => array('border: 0px;'),
			),
    	);

    $form['personal_data']['column_left']['submit'] = array(
    	'#type' => 'submit',
    	'#value' => t('Сохранить'),
    	);

    return $form;
}

function personal_student_data_form_submit($form, &$form_state)
{
	global $user;
//	debug($form_state['values']['Number']);

	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    mysqli_query($mysqli, "UPDATE student
    						SET `Passport` = '" . $form_state['values']['Number'] . $form_state['values']['Series'] . "',
    						`Surname` = '" . $form_state['values']['Surname'] . "', 
    						`FirstName` = '" . $form_state['values']['FirstName'] . "',
    						`Patronymic` = '" . $form_state['values']['Patronymic'] . "',
    						`Phone` = '" . $form_state['values']['Phone'] . "',
    						`E-mail` = '" . $form_state['values']['Email'] . "',
    						`Address` = '" . $form_state['values']['Address'] . "'
    						WHERE `idStudent` = '" . $form_state['storage']['student_id'] . "'
    						");
    $mysqli->close();

    $c_user=user_load($user->uid);
	$c_user->mail = $form_state['values']['Email'];
	user_save($c_user);

	drupal_set_message("Данные успешно изменены!");
}