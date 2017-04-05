<?php

function teachers_contracts_end_form($form, &$form_state)
{
	$form['start_date'] = array(
		'#type' => 'date',
		'#title' => 'Заканчиваются с:',
		'#default_value' => array(
			'year' => date('Y'),
			'month' => date('n'),
			'day' => date('j'),
			), 
		);

	$form['end_date'] = array(
		'#type' => 'date',
		'#title' => 'по:',
		'#default_value' => array(
			'year' => date('Y') + 1,
			'month' => date('n'),
			'day' => date('j'),
			), 
		);

	$form['submit'] = array(
		'#type' => 'submit',
		'#value' => 'Показать',
		'#ajax' => array(
            'wrapper' => 'table-result-wrapper', 
            'callback' => 'teachers_contracts_end_ajax_callback',
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

function teachers_contracts_end_ajax_callback($form, &$form_state)
{
	return $form['result'];
}

function teachers_contracts_end_form_submit($form, &$form_state)
{
	$start_date = $form_state['values']['start_date']['year']. '-' . $form_state['values']['start_date']['month'] . '-' . $form_state['values']['start_date']['day'];

	$end_date = $form_state['values']['end_date']['year']. '-' . $form_state['values']['end_date']['month'] . '-' . $form_state['values']['end_date']['day'];

	$server = 'localhost';
    $username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $teachers_result = mysqli_query($mysqli, "SELECT `idTeacher`, `Surname`, `FirstName`, `Patronymic`,
    	`Position`, `Contract`, `TerminationDate` 
    	FROM teacher
    	WHERE (`TerminationDate` >= STR_TO_DATE('" . $start_date . "', '%Y-%c-%e')  
    	AND `TerminationDate` <= STR_TO_DATE('" . $end_date . "', '%Y-%c-%e'))");

    $rows = array();

    foreach ($teachers_result as $row) 
    {
    	$rows[] = array(
    		"<a href='view?id=" . $row ["idTeacher"] . "'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
    		$row['Surname'],
    		$row['FirstName'],
    		$row['Patronymic'],
    		$row['Position'],
    		$row['Contract'],
    		$row['TerminationDate'],);
    }
    if(!empty($teachers_result))
    	$teachers_result->close();

    $header = array(
    	'',
    	'Фамилия',
    	'Имя',
    	'Отчество',
    	'Должность',
    	'Тип договора',
    	'Дата окончания',
    	);

	$form_state['storage']['table'] = theme('table', array('header' => $header, 'rows' => $rows));
	$form_state['rebuild'] = TRUE;
}