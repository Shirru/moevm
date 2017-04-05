<?php

function teachers_stafflist_page()
{
	return drupal_get_form ( 'teachers_stafflist_form' );
}

function teachers_stafflist_form($form, &$form_state)
{
	$form['#tree'] = TRUE;

	$form['file'] = array(
		'#title' => 'Выберите файл со штатным расписанием',
        '#type' => 'file',
      	);

	$form['load_file'] = array(
		'#value' => 'Загрузить',
        '#type' => 'submit',
        '#ajax' => array(
	        'wrapper' => 'teachers-stafflist-form-results-wrapper', 
	        'callback' => 'teachers_stafflist_form_ajax_callback',
	        ),
      	);

	$form['results'] = array(
		'#prefix' => '<div id = "teachers-stafflist-form-results-wrapper">',
		'#suffix' => '</div>',
		/*'#type' => 'fieldset',
		'#collapsible' => TRUE, 
		'#collapsed' => FALSE, 
		'#title' => 'Результат',*/
		);

	if (!empty($form_state['storage']['results']))
	{
		if(!empty($form_state['storage']['results']['table_new']))
		{
			$form['results']['new']['table'] = array(
				'#prefix' => '<br><h3>Новые преподаватели</h3>',
				'#markup' => $form_state['storage']['results']['table_new'],
				);

			$form['results']['new']['add'] = array(
				'#type' => 'submit',
				'#value' => 'Добавить',
				'#ajax' => array(
			        'wrapper' => 'teachers-stafflist-form-results-wrapper', 
			        'callback' => 'teachers_stafflist_form_ajax_callback',
			        ),
				);
		}
		
		if(!empty($form_state['storage']['results']['table_change']))
		{
			$form['results']['change']['table'] = array(
				'#prefix' => '<br><h3>Изменения</h3>',
				'#markup' => $form_state['storage']['results']['table_change'],
				);

			$form['results']['change']['set_change'] = array(
				'#type' => 'submit',
				'#value' => 'Внести изменения',
				'#ajax' => array(
			        'wrapper' => 'teachers-stafflist-form-results-wrapper', 
			        'callback' => 'teachers_stafflist_form_ajax_callback',
			        ),
				);
		}
	
		if(!empty($form_state['storage']['results']['table_not']))
		{
			$form['results']['not']['table'] = array(
				'#prefix' => '<br><h3>Нет в штатном расписании</h3>',
				'#markup' => $form_state['storage']['results']['table_not'],
				);

			$form['results']['not']['delete'] = array(
				'#type' => 'submit',
				'#value' => 'Поставить состояние Уволен',
				'#ajax' => array(
			        'wrapper' => 'teachers-stafflist-form-results-wrapper', 
			        'callback' => 'teachers_stafflist_form_ajax_callback',
			        ),
				);
		}
	}

	return $form;
}

function teachers_stafflist_form_ajax_callback($form, &$form_state) 
{
    return $form['results'];
}

function teachers_stafflist_form_validate($form, &$form_state) 
{
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

function teachers_stafflist_form_submit($form, &$form_state)
{
	if (isset($form['load_file']['#value']) && $form_state['triggering_element']['#value'] == $form['load_file']['#value']) 
	{
		require_once libraries_get_path('Classes') . "/PHPExcel.php";

	    $path = 'public://documents/';
	    $path = drupal_realpath($path);

	    $file = $form_state['values']['file'];

	    $excel = PHPExcel_IOFactory::load($path . "/" . $file->filename);
	    $teachers_list = teachers_stafflist_parse($excel);
	    $results = teachers_stafflist_get_results($teachers_list);

	    $form_state['storage']['results_for_db'] =  $results;

	    $header = array('Ф.И.О.', 'Должность', 'Степень', 'Доля ставки', 'Вид договора');
		$rows = array();

		if (!empty($results['new']))
			$form_state['storage']['results']['table_new'] = theme('table', array('header' => $header, 'rows' => $results['new']));
		if (!empty($results['change']))
			$form_state['storage']['results']['table_change'] = theme('table', array('header' => $header, 'rows' => $results['change']));
		if (!empty($results['not']))
			$form_state['storage']['results']['table_not'] = theme('table', array('header' => $header, 'rows' => $results['not']));

		if(empty($results['new']) && empty($results['change']) && empty($results['not']))
			drupal_set_message('Изменений нет');

		file_delete($file);

	}

	if (isset($form['results']['new']['add']['#value']) && $form_state['triggering_element']['#value'] == $form['results']['new']['add']['#value']) 
	{
		$server = 'localhost';
		$username = 'moevm_user';
		$password = 'Pwt258E6JT8QAz3y';
		$database = 'moevmdb';

		$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
  		mysqli_query ($mysqli, "SET NAMES `utf8`");

  		$results_new =  $form_state['storage']['results_for_db']['new'];

  		foreach ($results_new as $row)
  		{
  			$name = preg_split("/[\s]+/", $row['FIO']);

  			mysqli_query ($mysqli, "INSERT INTO teacher
  									(`Surname`, `Initials`, `Position`, `ShareRates`, `Degree`, `Contract`)
  									VALUES ('" . $name[0] . "',
  									'" .  $name[1] . "', 
  									'" . $row['Position'] . "',
  									'" . $row['ShareRates'] . "',
  									'" . $row['Degree'] . "',
  									'" . $row['Contract'] . "')
  									");
  		}
  		$mysqli->close();
  		$form_state['storage']['results']['table_new'] = NULL;
	}

	if (isset($form['results']['change']['set_change']['#value']) && $form_state['triggering_element']['#value'] == $form['results']['change']['set_change']['#value']) 
	{
		$server = 'localhost';
		$username = 'moevm_user';
		$password = 'Pwt258E6JT8QAz3y';
		$database = 'moevmdb';

		$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
  		mysqli_query ($mysqli, "SET NAMES `utf8`");

  		$results_change =  $form_state['storage']['results_for_db']['change'];

  		foreach ($results_change as $row)
  		{
  			$name = preg_split("/[\s]+/", $row['FIO']);
  		
  			$is_success = mysqli_query ($mysqli, "UPDATE teacher
  									SET `Position` = '" . $row['Position'] . "',
  									`ShareRates` = '" . $row['ShareRates'] . "',
  									`Degree` = '" . $row['Degree'] . "',
  									`Contract` = '" . $row['Contract'] . "'
  									WHERE (`Surname` = '" . $name[0] . "' AND
  									(SELECT LEFT(`FirstName`, 1)) = '" . mb_substr($name[1], 0, 1) . "' AND
  									(SELECT LEFT(`Patronymic`, 1)) = '" . mb_substr($name[1], 2, 1) . "')
  									");
  			if(!$is_success)
  			{
  				mysqli_query ($mysqli, "UPDATE teacher
  									SET `Position` = '" . $row['Position'] . "',
  									`ShareRates` = '" . $row['ShareRates'] . "',
  									`Degree` = '" . $row['Degree'] . "',
  									`Contract` = '" . $row['Contract'] . "' 
  									WHERE (`Surname` = '" . $name[0] . "' AND
  									`Initials` = '" . $name[1] . "')
  									");
  			}
  		}
  		$mysqli->close();
  		$form_state['storage']['results']['table_change'] = NULL;
	}

	if (isset($form['results']['not']['delete']['#value']) && $form_state['triggering_element']['#value'] == $form['results']['not']['delete']['#value']) 
	{
		$server = 'localhost';
		$username = 'moevm_user';
		$password = 'Pwt258E6JT8QAz3y';
		$database = 'moevmdb';
		
		$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
  		mysqli_query ($mysqli, "SET NAMES `utf8`");

  		$results_not =  $form_state['storage']['results_for_db']['not'];

  		foreach ($results_not as $row)
  		{
  			$name = preg_split("/[\s]+/", $row['FIO']);

  			$is_success = mysqli_query ($mysqli, "UPDATE teacher
  									SET `Condition` = 'Уволен'
  									WHERE (`Surname` = '" . $name[0] . "' AND
  									(SELECT LEFT(`FirstName`, 1)) = '" . mb_substr($name[1], 0, 1) . "' AND
  									(SELECT LEFT(`Patronymic`, 1)) = '" . mb_substr($name[1], 2, 1) . "')
  									");
  			if(!$is_success)
  			{
  				mysqli_query ($mysqli, "UPDATE teacher
  									SET `Condition` = 'Уволен'
  									WHERE (`Surname` = '" . $name[0] . "' AND
  									`Initials` = '" . $name[1] . "')
  									");
  			}
  		}
  		$mysqli->close();
  		$form_state['storage']['results']['table_not'] = NULL;
	}

	$form_state['rebuild'] = TRUE;	
}

//на вход подается загруженная книга excel
function teachers_stafflist_parse($stafflist)
{
	$nums_of_columns = array();
    require_once libraries_get_path('Classes') . "/PHPExcel.php";

    $stafflist->setActiveSheetIndex(1);
    $current_sheet = $stafflist->getActiveSheet();

    foreach( $current_sheet->getRowIterator() as $row ) 
    {
    	if(count($nums_of_columns) == 6) break;
        foreach( $row->getCellIterator() as $cell ) 
        {
            if(count($nums_of_columns) == 6) break;
            $value = $cell->getValue();
            switch ($value) 
            {
                case '№ п/п':
                    $nums_of_columns["Number"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;

                case 'Ф.И.О.':
                    $nums_of_columns["FIO"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;

                case 'Наименование должности':
                    $nums_of_columns["Position"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;

                case 'Квалификация (ученая степень)':
                    $nums_of_columns["Degree"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;

                case 'Доля ставки':
                    $nums_of_columns["ShareRates"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;

                case 'Форма привлечения':
                    $nums_of_columns["Contract"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
            }
        }
    }
    
    if(!empty($nums_of_columns))
    {
    	 $teachers_list = array();

	    $highest_row = $current_sheet->getHighestRow();
	    for ($row = 1; $row <= $highest_row; $row++)
	    {
	        $cell = $current_sheet->getCellByColumnAndRow($nums_of_columns["Number"], $row);
	        $value = $cell->getValue();

	        $cell_fio = $current_sheet->getCellByColumnAndRow($nums_of_columns["FIO"], $row);
	        $value_fio = $cell_fio->getValue();
	        if(is_numeric($value) && $value_fio != 'Вакансия')
	        {
	            $teachers_list[] = array(
	            "FIO" => $value_fio,
	            "Position" => $current_sheet->getCellByColumnAndRow($nums_of_columns["Position"], $row)->getValue(),
	            "Degree" => $current_sheet->getCellByColumnAndRow($nums_of_columns["Degree"], $row)->getValue(),
	            "ShareRates" => number_format($current_sheet->getCellByColumnAndRow($nums_of_columns["ShareRates"], $row)->getValue(), 2),
	            "Contract" => $current_sheet->getCellByColumnAndRow($nums_of_columns["Contract"], $row)->getValue(),
	            );     
	        }
	    }

	    array_shift($teachers_list);
	    return $teachers_list;
    }
    else return false;
}

function teachers_stafflist_get_results($teachers_list)
{
	$output = "";
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

	$results = array(
		'new' => array(),
		'change' => array(),
		'not' => array(),
		);

  	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
  	mysqli_query ($mysqli, "SET NAMES `utf8`");

  	$teachers_result = $mysqli->query("SELECT *
                 FROM teacher");

  	$teachers = array();

  	$i = 0;
  	foreach ($teachers_result as $row) 
  	{	
  		if(empty($row['Initials']))
  			$teachers[$i]['FIO'] = $row['Surname'] . ' ' . mb_substr($row['FirstName'], 0, 1) . '.' . mb_substr($row['Patronymic'], 0, 1) . '.';
  		else
  			$teachers[$i]['FIO'] = $row['Surname'] . ' ' . $row['Initials'];
  		$teachers[$i]['Position'] = $row['Position'];
  		$teachers[$i]['Degree'] = $row['Degree'];
  		$teachers[$i]['ShareRates'] = $row['ShareRates'];
  		$teachers[$i]['Condition'] = $row['Condition'];
  		$teachers[$i]['Contract'] = $row['Contract'];

  		$i++;
  	}

  	$teachers_result->close();
	$mysqli->close();

	for($i = 0; $i < count($teachers_list); $i++) 
  	{
  		$name = preg_split("/[\s]+/", $teachers_list[$i]['FIO']);
  		$teachers_list[$i]['FIO'] = $name[0] . ' ' . $name[1];
  	}	

	$is_found_in_sl = FALSE;

	foreach($teachers as $teacher_from_db) 
	{
		foreach($teachers_list as $teacher_from_sl)
		{
			if(strcasecmp($teacher_from_db['FIO'], $teacher_from_sl['FIO']) == 0)
			{
				// Если преподаватель из БД есть в ШР, то проверяем, все ли данные совпадают
				$is_found_in_sl = TRUE;

				$is_equal_position = strcasecmp(trim($teacher_from_db['Position']), trim($teacher_from_sl['Position'])) == 0 ? TRUE : FALSE;
				$is_equal_degree = strcasecmp(trim($teacher_from_db['Degree']), trim($teacher_from_sl['Degree'])) == 0 ? TRUE : FALSE;
				$is_equal_sharerates = $teacher_from_db['ShareRates'] == $teacher_from_sl['ShareRates'] ? TRUE : FALSE;
				$is_equal_contract = $teacher_from_db['Contract'] == $teacher_from_sl['Contract'] ? TRUE : FALSE;

				if(!($is_equal_position && $is_equal_degree && $is_equal_sharerates && $is_equal_contract))
				{
					$results['change'][] = $teacher_from_sl;
				}
				break;
			}
			else
			{
				$is_found_in_sl = FALSE;
			}
		}

		if(!$is_found_in_sl && !strcasecmp($teacher_from_db['Condition'], 'Уволен') == 0)
		{
			array_pop($teacher_from_db);
			$results['not'][] = $teacher_from_db;
		}
		$is_found_in_sl = FALSE;
	}

	$is_found_in_db = FALSE;
	foreach($teachers_list as $teacher_from_sl) 
	{
		foreach($teachers as $teacher_from_db)
		{

			if(strcasecmp($teacher_from_db['FIO'], $teacher_from_sl['FIO']) == 0 )
			{
				// Если преподаватель из ШР есть в БД, то все ок, их мы уже проверили
				$is_found_in_db = TRUE;

				if(strcasecmp($teacher_from_db['Condition'], 'Уволен') == 0)
					$is_found_in_db = FALSE;

				break;
			}
			else
			{
				$is_found_in_db = FALSE;
			}
		}

		if(!$is_found_in_db)
		{
			$results['new'][] = $teacher_from_sl;
		}
		$is_found_in_db = FALSE;
	}

	return $results;
}