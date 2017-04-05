<?php

function teachers_anniversary_form($form, &$form_state)
{
	$form['year'] = array(
		'#type' => 'select',
		'#title' => 'Год',
		'#options' => range(date('Y') - 5, date('Y') + 10),
		'#default_value' => 5,
		);

	$form['submit'] = array(
		'#type' => 'submit',
		'#value' => 'Показать',
		'#ajax' => array(
            'wrapper' => 'table-result-wrapper', 
            'callback' => 'teachers_anniversary_ajax_callback',
            ),
		);

	$form['result'] = array(
		'#prefix' => '<div id = "table-result-wrapper">',
		'#suffix' => '</div>',
		);

	if(isset($form_state['storage']['table']))
	{
		$form['result']['table'] = array(
			'#markup' => $form_state['storage']['table'],
			);
	}

	return $form;
}

function teachers_anniversary_ajax_callback($form, &$form_state)
{
	return $form['result'];
}

function teachers_anniversary_form_submit($form, &$form_state)
{
	$year = $form_state['complete form']['year']['#options'][$form_state['values']['year']];

	$server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $teachers_result = mysqli_query($mysqli, "SELECT `Surname`, `FirstName`, `Patronymic`,
    	`Position`, `BirthDate`, '" . date('Y') . "' - YEAR(`BirthDate`) AS `Age`  
    	FROM teacher
    	WHERE ('" . date('Y') . "' - YEAR(`BirthDate`)) % 5 = 0");

    $rows = array();

    foreach ($teachers_result as $row) 
    {
    	$rows[] = array(
    		$row['Surname'],
    		$row['FirstName'],
    		$row['Patronymic'],
    		$row['Position'],
    		$row['BirthDate'],
    		$row['Age'],);
    }
    if(!empty($teachers_result))
    	$teachers_result->close();

    $header = array(
    	'Фамилия',
    	'Имя',
    	'Отчество',
    	'Должность',
    	'Дата рождения',
    	'Возраст',
    	);

	$form_state['storage']['table'] = theme('table', array('header' => $header, 'rows' => $rows));
	$form_state['rebuild'] = TRUE;
}