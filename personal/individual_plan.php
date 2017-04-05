<?php

function personal_teacher_individual_plan()
{
	global $user;
	$email = $user->mail;

	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
    $output = '';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $teacher_result = $mysqli->query("SELECT *
                 FROM teacher
                 WHERE `E-Mail` = '" . $email . "'");
    
	$teacher = $teacher_result->fetch_assoc();
	$teacher_result->close();

	$ind_plan_result = $mysqli->query("SELECT *
                 FROM individualplan
                 WHERE `Teacher` = '" . $teacher['idTeacher'] . "'");

	$mysqli->close();

	// Если преподаватель уже загрузил ИП
	if($ind_plan_result)
	{
		$ind_plan = $ind_plan_result->fetch_assoc();
		$ind_plan_result->close();
		if($ind_plan["WordFile"] && $ind_plan["ExcelFile"])
		{
			// Если уже сентябрь нового учебного года, то необходимо загрузить новый УП
			if(date('Y') == substr($ind_plan['AcademicYear'], 5, 4) && date('n') >= 9)
			{
				$form = drupal_get_form('personal_teacher_individual_plan_load_form');
				$output = render($form);
				return $output;
			}
			else
			{
				$form = drupal_get_form('personal_teacher_individual_plan_change_form');
				$output = render($form);
				return $output;
			}
		}
		else 
		{	
			$form = drupal_get_form('personal_teacher_individual_plan_load_form');
			$output = render($form);
			return $output;
		}
	}
	else // Если не загружал
	{
		$form = drupal_get_form('personal_teacher_individual_plan_load_form');
		$output = render($form);
		return $output;
	}
	
}

function personal_teacher_individual_plan_load_form($form, &$form_state)
{
	global $user;
	$email = $user->mail;

	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $teacher_result = $mysqli->query("SELECT *
                 FROM teacher
                 WHERE `E-Mail` = '" . $email . "'");
    
	$teacher = $teacher_result->fetch_assoc();
	$teacher_result->close();

	$form_state['storage']['teacher_id'] = $teacher['idTeacher'];

	$mysqli->close();

	// Индивидуальный план
    $form['individual_plan'] = array(
        '#type' => 'fieldset',
        '#collapsible' => TRUE, 
		'#collapsed' => TRUE, 
        '#title' => 'Индивидуальный план',
      	);

	$form['individual_plan']['description'] = array(
		'#markup' => t('Индивидуальный план состоит из текстового файла (doc/docx/odt) и из файла электронных таблиц (xls/xlsx/xlsm). Выберите соответствующие файлы на Вашем коммпьютере и нажмите "Загрузить". <br>'),
		);

	$form['individual_plan']['file_one'] = array(
        '#type' => 'file',
      	);

	$form['individual_plan']['file_two'] = array(
        '#type' => 'file',
      	);

    $form['individual_plan']['submit'] = array(
    	'#type' => 'submit',
    	'#value' => t('Загрузить'),
    	);
    
    
    return $form;
}



function personal_teacher_individual_plan_load_form_validate($form, &$form_state) 
{
    $validators = array(
    	'file_validate_extensions' => array('xlsx xls xlsm doc docx odt'), // Проверка на расширения
    );

    	$new_path = 'public://documents/individual_plans/' . $form_state['storage']['teacher_id'] . '/';
		file_prepare_directory($new_path, FILE_CREATE_DIRECTORY);
    	if ($file_one = file_save_upload('file_one', $validators, $new_path, FILE_EXISTS_REPLACE)) 
	    {
	        if ($file_one) {
	  			$new_filename = date('YmdHis') . '.' . pathinfo($file_one->filename, PATHINFO_EXTENSION);
	  			$file_one = file_move($file_one, $new_path . $new_filename);
	  			$form_state['values']['file_one'] = $file_one; 
	  			//debug($file_one);
	  			$form_state['storage']['file_one_copy'] = file_copy($file_one, $new_path, FILE_EXISTS_RENAME);
	  			//debug($form_state['storage']['file_one_copy']);
			}
	    }
	    else 
	    {
	        form_set_error('file_one', 'Файл не был выбран');
	    }

    	if ($file_two = file_save_upload('file_two', $validators, $new_path, FILE_EXISTS_REPLACE)) 
	    { 
	        if ($file_two) {
	  			$new_filename = date('YmdHis') . '.' . pathinfo($file_two->filename, PATHINFO_EXTENSION);
	  			$file_two = file_move($file_two, $new_path . $new_filename);
	  			$form_state['values']['file_two'] = $file_two;
	  			$form_state['storage']['file_two_copy'] = file_copy($file_two, $new_path, FILE_EXISTS_RENAME);
			}
	    }
	    else 
	    {
	        form_set_error('file_two', 'Файл не был выбран');
	    }

   
}

function personal_teacher_individual_plan_load_form_submit($form, &$form_state)
{
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

    $year = "";
	$year .= date('Y') . '-' . (date('Y') + 1);
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

	if(isset($form_state['values']['file_one']) && isset($form_state['values']['file_two']))
	{
		$file_one = $form_state['values']['file_one'];
		$file_one_copy = $form_state['storage']['file_one_copy'];
		$file_one_path = $file_one->uri;
	//	$file_one_path = str_replace("\\", "/", $file_one_path);
	//	debug($file_one_path);
		$file_one_copy_path = $file_one_copy->uri;
	//	$file_one_copy_path = str_replace("\\", "/", $file_one_copy_path);

		$file_two = $form_state['values']['file_two'];
		$file_two_copy = $form_state['storage']['file_two_copy'];
		$file_two_path = $file_two->uri;
		$file_two_copy_path = $file_two_copy->uri;

		$file_one->status = FILE_STATUS_PERMANENT; // Изменяем статус файла на "Постоянный"
  		file_save($file_one); // Сохраняем новый статус

  		$file_one_copy->status = FILE_STATUS_PERMANENT; // Изменяем статус файла на "Постоянный"
  		file_save($file_one_copy); // Сохраняем новый статус

  		$file_two->status = FILE_STATUS_PERMANENT; // Изменяем статус файла на "Постоянный"
  		file_save($file_two); // Сохраняем новый статус

  		$file_two_copy->status = FILE_STATUS_PERMANENT; // Изменяем статус файла на "Постоянный"
  		file_save($file_two_copy); // Сохраняем новый статус
		
		$ext_one = pathinfo($file_one->filename, PATHINFO_EXTENSION);
		$ext_two = pathinfo($file_two->filename, PATHINFO_EXTENSION);

		if(($ext_one == 'doc' || $ext_one == 'docx' || $ext_one == 'odt') && ($ext_two == 'xls' || $ext_two == 'xlsx' || $ext_two == 'xlsm'))
		{
			mysqli_query($mysqli, "INSERT INTO `individualplan`
									(`WordFile`, `ExcelFile`, `WordCopy`, `ExcelCopy`, `Teacher`, `AcademicYear`)
									VALUES (
									'" . $file_one_path . "',
									'" . $file_two_path . "',
									'" . $file_one_copy_path . "',
									'" . $file_two_copy_path . "',
									'" . $form_state['storage']['teacher_id'] . "',
									'" . $year . "')"
									);
			drupal_set_message("Файлы успешно загружены!");
		}
		else if(($ext_two == 'doc' || $ext_two == 'docx' || $ext_two == 'odt') && ($ext_one == 'xls' || $ext_one == 'xlsx' || $ext_one == 'xlsm'))
		{
			mysqli_query($mysqli, "INSERT INTO `individualplan`
									(`WordFile`, `ExcelFile`, `WordCopy`, `ExcelCopy`, `Teacher`, `AcademicYear`)
									VALUES (
									'" . $file_two_path . "',
									'" . $file_one_path . "',
									'" . $file_two_copy_path . "',
									'" . $file_one_copy_path . "',
									'" . $form_state['storage']['teacher_id'] . "',
									'" . $year . "')"
									);
			drupal_set_message("Файлы успешно загружены!");
		}
		else
		{
			drupal_set_message('Выбранные файлы не соответствуют заданным форматам', 'error');
		}

	}

	$mysqli->close();
}

function personal_teacher_individual_plan_change_form($form, &$form_state)
{
	$form['#prefix'] = '<div id="personal_teacher_individual_plan_change_form-wrapper">';
    $form['#suffix'] = '</div>';
    
    $form['#tree'] = TRUE;

	global $user;
	$email = $user->mail;

	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $teacher_result = $mysqli->query("SELECT *
                 FROM teacher
                 WHERE `E-Mail` = '" . $email . "'");
    
	$teacher = $teacher_result->fetch_assoc();
	$teacher_result->close();

	$form_state['storage']['teacher_id'] = $teacher['idTeacher'];

	$ind_plan_result = $mysqli->query("SELECT *
                 FROM individualplan
                 WHERE `Teacher` = '" . $teacher['idTeacher'] . "'");

	$mysqli->close();
	$ind_plan = $ind_plan_result->fetch_assoc();
	$ind_plan_result->close();

	//debug(drupal_realpath($ind_plan["WordFile"]));
	//debug(file_create_url($ind_plan["WordFile"]));
	$step = empty($form_state['storage']['step']) ? 1 : $form_state['storage']['step'];
    $form_state['storage']['step'] = $step;

    switch ($step) 
    {
        case 1:
			// Индивидуальный план
		    $form['step1'] = array(
		        '#type' => 'fieldset',
		        '#collapsible' => TRUE, 
				'#collapsed' => TRUE, 
		        '#title' => 'Индивидуальный план',
		      	);

			$form['step1']['description'] = array(
				'#markup' => t('Индивидуальный план состоит из текстового файла (doc/docx/odt) и из файла электронных таблиц (xls/xlsx).  <br>'),
				);

			$form['step1']['list'] = array(
				'#markup' => "<table><tr><td>Оригинал ИП." . pathinfo($ind_plan["WordFile"], PATHINFO_EXTENSION) . "</td><td><a href='" . file_create_url($ind_plan["WordFile"]) . "' download><img src = '/sites/all/pic/download.png'></a><a href=http://docs.google.com/viewer?url=" . file_create_url($ind_plan["WordFile"]) . "  title='просмотр'><img src = '/sites/all/pic/preview.png'></a></td></tr>
				<tr><td>Копия для редактирования ИП." . pathinfo($ind_plan["WordCopy"], PATHINFO_EXTENSION) . "</td><td><a href='" . file_create_url($ind_plan["WordCopy"]) . "' download><img src = '/sites/all/pic/download.png'></a><a href=http://docs.google.com/viewer?url=" . file_create_url($ind_plan["WordCopy"]) . "  title='просмотр'><img src = '/sites/all/pic/preview.png'></a></td></tr>
				<tr><td>Оригинал ИП." . pathinfo($ind_plan["ExcelFile"], PATHINFO_EXTENSION) . "</td><td><a href='" . file_create_url($ind_plan["ExcelFile"]) . "' download><img src = '/sites/all/pic/download.png'></a><a href=http://docs.google.com/viewer?url=" . file_create_url($ind_plan["ExcelFile"]) . "  title='просмотр'><img src = '/sites/all/pic/preview.png'></a></td></tr>
				<tr><td>Копия для редактирования ИП." . pathinfo($ind_plan["ExcelCopy"], PATHINFO_EXTENSION) . "</td><td><a href='" . file_create_url($ind_plan["ExcelCopy"]) . "' download><img src = '/sites/all/pic/download.png'></a><a href=http://docs.google.com/viewer?url=" . file_create_url($ind_plan["ExcelCopy"]) . "  title='просмотр'><img src = '/sites/all/pic/preview.png'></a></td></tr></table>"
				);

			$form['step1']['load_new'] = array(
		    	'#type' => 'submit',
		    	'#value' => t('Загрузить новую версию'),
		    	'#ajax' => array(
	                'wrapper' => 'personal_teacher_individual_plan_change_form-wrapper', 
	                'callback' => 'personal_teacher_individual_plan_change_ajax_callback',
	                ),
				);

		break;

		case 2:
			$form['step2'] = array(
		        '#type' => 'fieldset',
		        '#collapsible' => TRUE, 
				'#collapsed' => FALSE, 
		        '#title' => 'Индивидуальный план',
	      	);

			$form['step2']['description'] = array(
				'#markup' => t('Индивидуальный план состоит из текстового файла (doc/docx/odt) и из файла электронных таблиц (xls/xlsx). Выберите соответствующие файлы на Вашем коммпьютере и нажмите "Загрузить". <br>'),
				);

			$form['step2']['file_one'] = array(
				'#name' => 'files[file_one]',
		        '#type' => 'file',
		      	);

			$form['step2']['file_two'] = array(
				'#name' => 'files[file_two]',
		        '#type' => 'file',
		      	);

			$form['step2']['submit'] = array(
	            '#type' => 'submit', 
	            '#value' => 'Загрузить',
	            );

       		 $form['step2']['cancel'] = array(
	            '#type' => 'submit', 
	            '#value' => 'Отмена',
	            '#limit_validation_errors' => array(),
	            '#submit' => array('personal_teacher_individual_plan_change_form_submit'),
	            '#ajax' => array(
		                'wrapper' => 'personal_teacher_individual_plan_change_form-wrapper', 
		                'callback' => 'personal_teacher_individual_plan_change_ajax_callback',
		                ),
	            );

		    break;
	}

	return $form;
}

function personal_teacher_individual_plan_change_ajax_callback($form, &$form_state) 
{
    return $form;
}

function personal_teacher_individual_plan_change_form_validate($form, &$form_state)
{
	if (isset($form['step2']['submit']['#value']) && $form_state['triggering_element']['#value'] == $form['step2']['submit']['#value']) 
    {
        $validators = array(
            'file_validate_extensions' => array('xlsm xlsx xls doc docx odt'), // Проверка на расширения
        );

        $new_path = 'public://documents/individual_plans/' . $form_state['storage']['teacher_id'] . '/';
		file_prepare_directory($new_path, FILE_CREATE_DIRECTORY);
    	if ($file_one = file_save_upload('file_one', $validators, $new_path, FILE_EXISTS_REPLACE)) 
	    {
	        if ($file_one) {
	  			$new_filename = date('YmdHis') . '_copy.' . pathinfo($file_one->filename, PATHINFO_EXTENSION);
	  			$file_one = file_move($file_one, $new_path . $new_filename);
	  			$form_state['values']['file_one'] = $file_one; 
	  			//debug($file_one);
	  			//debug($form_state['storage']['file_one_copy']);
			}
	    }
	    else 
	    {
	        form_set_error('file_one', 'Файл не был выбран');
	    }

    	if ($file_two = file_save_upload('file_two', $validators, $new_path, FILE_EXISTS_REPLACE)) 
	    { 
	        if ($file_two) {
	  			$new_filename = date('YmdHis') . '_copy.' . pathinfo($file_two->filename, PATHINFO_EXTENSION);
	  			$file_two = file_move($file_two, $new_path . $new_filename);
	  			$form_state['values']['file_two'] = $file_two;
			}
	    }
	    else 
	    {
	        form_set_error('file_two', 'Файл не был выбран');
	    }
    }
}

function personal_teacher_individual_plan_change_form_submit($form, &$form_state)
{
	$current_step = 'step' . $form_state['storage']['step'];
    if (!empty($form_state['values'][$current_step])) 
    {
        $form_state['storage']['values'][$current_step] = $form_state['values'][$current_step];
    }

    if (isset($form['step1']['load_new']['#value']) && $form_state['triggering_element']['#value'] == $form['step1']['load_new']['#value']) 
    {
        $form_state['storage']['step']++;
        $step_name = 'step' . $form_state['storage']['step'];
        if (!empty($form_state['storage']['values'][$step_name])) 
        {
            $form_state['values'][$step_name] = $form_state['storage']['values'][$step_name];
        }
    }

    if (isset($form['step2']['cancel']['#value']) && $form_state['triggering_element']['#value'] == $form['step2']['cancel']['#value']) 
    {
       	$form_state['storage']['step']--;
      
        $step_name = 'step' . $form_state['storage']['step'];
        $form_state['values'][$step_name] = $form_state['storage']['values'][$step_name];
    }

    if(isset($form['step2']['submit']['#value']) && $form_state['triggering_element']['#value'] == $form['step2']['submit']['#value'])
    {
    	$server = 'localhost';
		$username = 'moevm_user';
		$password = 'Pwt258E6JT8QAz3y';
		$database = 'moevmdb';
	  
	    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	    mysqli_query ($mysqli, "SET NAMES `utf8`");

	    $year = "";
		$year .= date('Y') . '-' . (date('Y') + 1);

		$ind_plan_result = mysqli_query ($mysqli, "SELECT * FROM individualplan
										WHERE (`Teacher` = '" . $form_state['storage']['teacher_id'] . "'
										AND `AcademicYear` = '" . $year . "')");
		$ind_plan = $ind_plan_result->fetch_assoc();
		$ind_plan_result->close();

		if(isset($form_state['values']['file_one']) && isset($form_state['values']['file_two']))
		{
			$file_one = $form_state['values']['file_one'];
			$file_one_path = $file_one->uri;

			$file_two = $form_state['values']['file_two'];
			$file_two_path = $file_two->uri;
			
			$ext_one = pathinfo($file_one->filename, PATHINFO_EXTENSION);
			$ext_two = pathinfo($file_two->filename, PATHINFO_EXTENSION);

			if(($ext_one == 'doc' || $ext_one == 'docx' || $ext_one == 'odt') && ($ext_two == 'xls' || $ext_two == 'xlsx' || $ext_two == 'xlsm'))
			{
				mysqli_query($mysqli, "UPDATE `individualplan`
										SET `WordCopy` = '" . $file_one_path . "', 
										`ExcelCopy` = '" . $file_two_path . "'
										WHERE (`Teacher` = '" . $form_state['storage']['teacher_id'] . "'
										AND `AcademicYear` = '" . $year . "')"
										);
				$file_one->status = FILE_STATUS_PERMANENT; // Изменяем статус файла на "Постоянный"
		  		file_save($file_one); // Сохраняем новый статус

		  		$file_two->status = FILE_STATUS_PERMANENT; // Изменяем статус файла на "Постоянный"
		  		file_save($file_two); // Сохраняем новый статус
				drupal_set_message("Файлы успешно загружены!");

				// Удаление прошлых копий
				$path = $ind_plan["WordCopy"];
				$fid = db_query("SELECT fid FROM {file_managed} WHERE uri = :path", array(':path' => $path))->fetchField();
				$file = file_load($fid);
				file_delete($file);

				$path = $ind_plan["ExcelCopy"];
				$fid = db_query("SELECT fid FROM {file_managed} WHERE uri = :path", array(':path' => $path))->fetchField();
				$file = file_load($fid);
				file_delete($file);
			}
			else if(($ext_two == 'doc' || $ext_two == 'docx' || $ext_two == 'odt') && ($ext_one == 'xls' || $ext_one == 'xlsx' || $ext_one == 'xlsm'))
			{
				mysqli_query($mysqli, "UPDATE `individualplan`
										SET `WordCopy` = '" . $file_two_path . "', 
										`ExcelCopy` = '" . $file_one_path . "'
										WHERE (`Teacher` = '" . $form_state['storage']['teacher_id'] . "'
										AND `AcademicYear` = '" . $year . "')"
										);
				$file_one->status = FILE_STATUS_PERMANENT; // Изменяем статус файла на "Постоянный"
		  		file_save($file_one); // Сохраняем новый статус

		  		$file_two->status = FILE_STATUS_PERMANENT; // Изменяем статус файла на "Постоянный"
		  		file_save($file_two); // Сохраняем новый статус
				drupal_set_message("Файлы успешно загружены!");

				// Удаление прошлых копий
				$path = $ind_plan["WordCopy"];
				$fid = db_query("SELECT fid FROM {file_managed} WHERE uri = :path", array(':path' => $path))->fetchField();
				$file = file_load($fid);
				file_delete($file);

				$path = $ind_plan["ExcelCopy"];
				$fid = db_query("SELECT fid FROM {file_managed} WHERE uri = :path", array(':path' => $path))->fetchField();
				$file = file_load($fid);
				file_delete($file);
			}
			else
			{
				drupal_set_message('Выбранные файлы не соответствуют заданным форматам', 'error');
			}

		}

		$mysqli->close();
		drupal_goto("/personal");
    }
    $form_state['rebuild'] = TRUE;
}