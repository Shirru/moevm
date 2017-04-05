<?php

function teachers_add_form($form, &$form_state)
{
	$disciplines_options = get_moevm_disciplines();
	//списки для селектов
    	$positions = array('Не выбрано','доцент', 'ассистент', 'профессор', 'ст.препод.', 'Зав.кафедрой');
    	$degrees = array('Не выбрано', 'к.н.', 'д.н.');
    	$share_rates_opt = array('Не выбрано', '0.25', '0.50', '0.75', '1.00');
    	$ranks = array('Не выбрано', 'доцент', 'профессор');

	$default_position = 0;
	$default_degree = 0;
	$default_share_rates = 0;
	$default_rank = 0;

	$form['column_left'] = array(
		'#type' => 'container',
		'#attributes' => array(
			'class' => array('column-left'),
			'style' => array('float: left'),
			),
		);

	$form['column_left']['surname'] = array(
		'#type' => 'textfield',
		'#size' => 30,
		'#title' => 'Фамилия',
		);

	$form['column_left']['first_name'] = array(
		'#type' => 'textfield',
		'#size' => 30,
		'#title' => 'Имя',
		);

	$form['column_left']['patronymic'] = array(
		'#type' => 'textfield',
		'#size' => 30,
		'#title' => 'Отчество',
		);

	$form['column_left']['position'] = array(
		'#type' => 'select', 
		'#title' => t('Должность'), 
		'#options' => $positions,
		'#default_value' => $default_position, 
		);

	$form['column_left']['email'] = array(
		'#type' => 'textfield',
		'#size' => 30,
		'#title' => 'E-mail',
		);

	$form['column_right'] = array(
		'#type' => 'container',
		'#attributes' => array(
			'class' => array('column-right'),
			'style' => array('float: right'),
			),
		);

	$form['column_right']['degree'] = array(
		'#type' => 'select', 
		'#title' => t('Степень'), 
		'#options' => $degrees,
		'#default_value' => $default_degree,
		);

	$form['column_right']['rank'] = array(
		'#type' => 'select', 
		'#title' => t('Звание'), 
		'#options' => $ranks,
		'#default_value' => $default_rank, 
		);

	$form['column_right']['contract'] = array(
		'#type' => 'textfield',
		'#size' => 30,
		'#title' => 'Вид договора',
		);

	$form['column_right']['share_rates'] = array(
			'#type' => 'select', 
			'#title' => t('Доля ставки'), 
			'#options' => $share_rates_opt,
			'#default_value' => $default_share_rates, 
	    	);


	$form['column_right']['conclusion_date'] = array(
		'#type' => 'date',
		'#title' => 'Дата заключения контракта',
		);

  	$form['can_teach_block'] = array(
  		'#prefix' => '<div id = "can-teach-div">',
    	'#suffix' => '</div>',
    	'#type' => 'fieldset',
    	'#title' => 'Может вести',
    	'#attributes' => array(
			'style' => array('clear: left'),
			),
  	);

  	if(isset($form_state['values']))	
  	{
  		$canteach_count = $form_state['storage']['count'];
  		if($form_state['values']['discipline_select' . $canteach_count] != 0)
  		{
  			$form_state['storage']['count'] ++;
  		}

  		$canteach_count = $form_state['storage']['count'];
  	}
  	else
  	{
  		$canteach_count = 1;
  		$form_state['storage']['count'] = 1;
  	}

	for ($i = 1; $i <= $canteach_count; $i++) 
	{
		
		$form['can_teach_block']['discipline_select' . $i] = array(
	      	'#type' => 'select', 
	      	'#options' => $disciplines_options,
    	  	'#default_value' => 0,
    	  	'#ajax' => array(
			    'callback' => 'teachers_add_form_ajax_callback',
			    'wrapper' => 'can-teach-div',
			    'event' => 'change',
			    ),
    		);  
	}

	$form['submit'] = array(
		'#type' => 'submit',
		'#value' => 'Добавить',
		);

	return $form;
}

function teachers_add_form_ajax_callback($form, &$form_state) 
{
  	return $form['can_teach_block'];
}

function teachers_add_form_submit($form, &$form_state) 
{
	$canteach_count = isset($form_state['storage']) ? $form_state['storage']['count'] : 1;

	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	$disciplines = array();

 	$position = $form_state['values']['position'] == 0 ? '' : $form['column_left']['position']['#options'][$form_state['values']['position']];
    	$share_rates = $form_state['values']['share_rates'] == 0 ? '' : $form['column_right']['share_rates']['#options'][$form_state['values']['share_rates']];
    	$degree = $form_state['values']['degree'] == 0 ? '' : $form['column_right']['degree']['#options'][$form_state['values']['degree']];
    	$rank = $form_state['values']['rank'] == 0 ? '' : $form['column_right']['rank']['#options'][$form_state['values']['rank']];

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$is_success = mysqli_query ($mysqli, "INSERT INTO `teacher`
		(`Surname`, `FirstName`, `Patronymic`, `Position`, `E-mail`, `Degree`, `Rank`, `ShareRates`, `Contract`, `ConclusionDate`)
		VALUES ('" . $form_state['values']['surname'] . "',
		'" . $form_state['values']['first_name'] . "',
		'" . $form_state['values']['patronymic'] . "',
		'" . $position . "',
		'" . $form_state['values']['email'] . "',
		'" . $degree . "',
		'" . $rank . "',
		'" . $share_rates . "',
		'" . $form_state['values']['contract'] . "',
		'" . $form_state['values']['conclusion_date']['year'] . '-' . $form_state['values']['conclusion_date']['month'] . '-' . $form_state['values']['conclusion_date']['day'] . "')");

	$teacher_result = mysqli_query ($mysqli, "SELECT `idTeacher` FROM `teacher`
		WHERE `E-mail` = '" . $form_state['values']['email'] . "'");

	$teacher = $teacher_result->fetch_assoc();
	$teacher_result->close();

	for($i = 1; $i <= $canteach_count; $i++)
	{
		if($form_state['complete form']['can_teach_block']['discipline_select' . $i]['#options'][$form_state['values']['discipline_select' . $i]] != 'Выберите дисциплину')
		{
			$discipline_result = mysqli_query ($mysqli, "SELECT `idDiscipline` FROM `discipline`
			WHERE `DisFullName` = '" . $form_state['complete form']['can_teach_block']['discipline_select' . $i]['#options'][$form_state['values']['discipline_select' . $i]] . "'");

			$discipline = $discipline_result->fetch_assoc();
			$discipline_result->close();

			$is_success = mysqli_query ($mysqli, "INSERT INTO `canteach`
				(`Discipline`, `Teacher`)
				VALUES ('" . $discipline['idDiscipline'] . "',
				'" . $teacher['idTeacher'] . "')");
		}
	}

	if($is_success)
		drupal_set_message('Данные добавлены успешно!');
	else
		drupal_set_message('Произошла ошибка при сохранении данных', 'error');

	$mysqli->close();
	drupal_goto('teachers');
}

function get_moevm_disciplines()
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
	$disciplines = array();

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$chair_result = mysqli_query ($mysqli, "SELECT `idChair` FROM `chair`
		WHERE `ChairNum` = 14");

	$chair = $chair_result->fetch_assoc();
	$chair_result->close();

	$disciplines_result = mysqli_query ($mysqli, "SELECT `DisFullName` FROM `discipline`
		WHERE `Chair` = '" . $chair['idChair'] . "'");

	$disciplines[] = 'Выберите дисциплину';
	foreach ($disciplines_result as $discipline) 
	{
		$disciplines[] = $discipline['DisFullName'];
	}

	$disciplines_result->close();
	$mysqli->close();

	return $disciplines;
}