<?php

function groups_moevm_add_group_form($form, &$form_state)
{
	$curriculum_nums = get_curriculum_nums();

	$form['number'] = array(
		'#type' => 'textfield',
		'#title' => 'Номер группы',
		'#size' => 30,
		);

	$form['curriculum'] = array(
		'#type' => 'select',
		'#title' => 'Номер УП',
		'#options' => $curriculum_nums,
		'#default_value' => 0,
		);

	$form['file'] = array(
		'#type' => 'file',
		'#title' => 'Выберите файл', 
		);

	$form['load_students'] = array(
		'#type' => 'submit',
		'#value' => 'Загрузить студентов из файла',
		'#ajax' => array(
	        'wrapper' => 'load-students-result-wrapper', 
	        'callback' => 'groups_moevm_add_group_load_ajax_callback',
	        ),
		);

	$form['load_result'] = array(
		'#prefix' => '<div id = "load-students-result-wrapper">',
		'#suffix' => '</div>',
		);

	if(!empty($form_state['storage']['result']))
	{
		$form['load_result']['result_fieldset'] = array(
			'#type' => 'fieldset',
			'#title' => 'Результат загрузки файла',
			'#collapsible' => true,
			'#collapsed' => false,
			);

		$form['load_result']['result_fieldset']['table'] = array(
			'#markup' => $form_state['storage']['result'],
			);

		$form['load_result']['result_fieldset']['cancel'] = array(
			'#type' => 'submit',
			'#value' => 'Отмена',
			);
	}

	$form['save'] = array(
		'#type' => 'submit',
		'#value' => 'Сохранить',
		);

	return $form;
}

function get_curriculum_nums()
{
	$server = 'localhost';
    $username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
    $nums = array();
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $curriculum_result = mysqli_query($mysqli, "SELECT `CurriculumNum` FROM `curriculum`");
    
    foreach ($curriculum_result as $num) 
    {
    	$nums[] = $num['CurriculumNum'];
    }

    $curriculum_result->close();
    $mysqli->close();

	return $nums;
}

function groups_moevm_add_group_load_ajax_callback($form, &$form_state)
{
	return $form['load_result'];
}

function groups_moevm_add_group_form_validate($form, &$form_state) 
{
	if (isset($form['load_students']['#value']) && $form_state['triggering_element']['#value'] == $form['load_students']['#value']) 
	{
		if (empty($form_state['values']['number']) || !is_numeric($form_state['values']['number'])) 
		{
	    	form_set_error('number', t('Некорректное значение поля, введите номер группы'));
	  	}
		
		$validators = array(
	        'file_validate_extensions' => array('xlsx xls xlsm'), // Проверка на расширения
	    	);

	    if ($file = file_save_upload('file', $validators, 'public://documents/')) 
	    {

		  	$new_filename = date('YmdHis') . '.' . pathinfo($file->filename, PATHINFO_EXTENSION);
		  	$file = file_move($file, 'public://documents/' . $new_filename);  		

	        $form_state['values']['file'] = $file; 
	    }
	    else 
	    {
	        form_set_error('file', 'Файл не был загружен');
	    }

	}
  
}

function groups_moevm_add_group_form_submit($form, &$form_state)
{
	if (isset($form['load_students']['#value']) && $form_state['triggering_element']['#value'] == $form['load_students']['#value']) 
	{
		require_once libraries_get_path('Classes') . "/PHPExcel.php";

	    $path = 'public://documents/';
	    $path = drupal_realpath($path);

	    $file = $form_state['values']['file'];

	    $excel = PHPExcel_IOFactory::load($path . "/" . $file->filename);
	    $students = parse_students($excel, $form_state['values']['number']);

	    $header = array('Фамилия', 'Имя', 'Отчество', 'Номер зачётной книжки');
	    $form_state['storage']['result'] = theme('table', array('header' => $header, 'rows' => $students));
	    $form_state['storage']['students'] = $students;

	    file_delete($file);
	}

	if (isset($form['load_result']['result_fieldset']['cancel']['#value']) && $form_state['triggering_element']['#value'] == $form['load_result']['result_fieldset']['cancel']['#value']) 
	{
		$form_state['storage']['result'] = NULL;
		$form_state['storage']['students'] = NULL;
	}

	if (isset($form['save']['#value']) && $form_state['triggering_element']['#value'] == $form['save']['#value']) 
	{
		$students = isset($form_state['storage']['students']) ? $form_state['storage']['students'] : null;
		$size = empty($students) ? 0 : count($students);
		$creation_year = creation_year($form_state['values']['number']);
		save_group_in_db($form_state['values']['number'], $creation_year, $form_state['complete form']['curriculum']['#options'][$form_state['values']['curriculum']], $size);

		if(!empty($form_state['storage']['students']))
			save_students_in_db($form_state['storage']['students'], $form_state['values']['number']);

		drupal_goto('groups/moevm');
	}

	$form_state['rebuild'] = TRUE;
}

function parse_students($student_list, $group_num)
{
    require_once libraries_get_path('Classes') . "/PHPExcel.php";
    $students = array();

    $student_list->setActiveSheetIndex(0);
    $current_sheet = $student_list->getActiveSheet();

    foreach($current_sheet->getRowIterator() as $row ) 
    {
        foreach( $row->getCellIterator() as $cell ) 
        {
            $value = $cell->getValue();
            if(stripos($value, 'гр.') !== false)
            {
            	$column_num = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
            	break;
            }
        }
    }

    $highest_row = $current_sheet->getHighestRow();
    for ($row = 1; $row <= $highest_row; $row++)
    {
        $cell = $current_sheet->getCellByColumnAndRow($column_num, $row);
        $value = $cell->getValue();

        if(stripos($value, $group_num) !== false)
        {
        	$row_num = 	$cell->getRow();
        	break;
        }
    }

    for($row = $row_num + 1; $row <= $highest_row; $row++)
    {
    	$cell = $current_sheet->getCellByColumnAndRow($column_num, $row);
        $value = $cell->getValue();

        $first_name = $current_sheet->getCellByColumnAndRow($column_num + 1, $row)->getValue();
        $patronymic = $current_sheet->getCellByColumnAndRow($column_num + 2, $row)->getValue();
        $record_book = $current_sheet->getCellByColumnAndRow($column_num + 3, $row)->getValue();

        if(stripos($value, 'гр.') !== false)
        	break;

        $students[] = array(
        	'surname' => $value,
        	'first_name' => $first_name,
        	'patronymic' => $patronymic,
        	'record_book' => $record_book,
        	);
    }

    return $students;
}

function save_students_in_db($students, $group_num)
{
	$server = 'localhost';
    $username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $group_result = mysqli_query ($mysqli, "SELECT `idGroup`
    	FROM `group`
    	WHERE `GroupNum` = '" . $group_num . "'");

	$group = $group_result->fetch_assoc();
	$group_result->close();

    foreach ($students as $student) 
    {
    	$is_success = mysqli_query ($mysqli, "INSERT INTO `student`
		(`RecordBookNum`, `Surname`, `FirstName`, `Patronymic`, `Group`)
		VALUES ('" . $student['record_book'] . "', 
		 '" . $student['surname'] . "', 
		 '" . $student['first_name'] . "', 
		 '" . $student['patronymic'] . "',	
		 '" . $group['idGroup'] . "')");
    }
    $mysqli->close();

    if($is_success)
    	drupal_set_message('Данные успешно сохранены!');
    else
    	drupal_set_message('Произошла ошибка при сохранении данных', 'error');
}

function save_group_in_db($number, $year, $curriculum_num, $size)
{
	$server = 'localhost';
    $username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $curriculum_result = mysqli_query ($mysqli, "SELECT `idCurriculum`
    	FROM `curriculum`
    	WHERE `CurriculumNum` = '" . $curriculum_num . "'");

	$curriculum = $curriculum_result->fetch_assoc();
	$curriculum_result->close();

	mysqli_query ($mysqli, "INSERT INTO `group`
		(`GroupNum`, `Size`, `CreationYear`, `Curriculum`)
		VALUES ('" . $number . "',
		'" . $size . "',
		 '" . $year . "',
		 '" . $curriculum['idCurriculum'] . "')");

	$mysqli->close();
}

function creation_year($group_num)
{
	$oldest_group_year = date('Y') - 6;
	$first_num = substr($group_num, 0, 1);
	$last_year = substr($oldest_group_year, 3, 1);
	$creation_year = $oldest_group_year + $first_num - $last_year;
	return $creation_year;
}