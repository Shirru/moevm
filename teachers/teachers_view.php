<?php
include 'teachers_view_canteach.php';
include 'teachers_view_hall_load.php';
include 'teachers_view_extra_load.php';

function teachers_view_page()
{
	$output = "";
	$teachers_view_form = drupal_get_form('teachers_view_form');
	$output .= render($teachers_view_form);

	$teachers_view_canteach_form = drupal_get_form('teachers_view_canteach_form');
	$output .= render($teachers_view_canteach_form);

	$teachers_view_hall_load_form = drupal_get_form('teachers_view_hall_load_form');
	$output .= render($teachers_view_hall_load_form);

	$teachers_view_extra_load_form = drupal_get_form('teachers_view_extra_load_form');
	$output .= render($teachers_view_extra_load_form);

	return $output;
}

function teachers_view_form($form, &$form_state)
{
	global $user;
    $is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);
    $is_denied = ($is_teacher || $is_student) && !$is_educational;

    //списки для селектов
    $positions = array('Не выбрано','доцент', 'ассистент', 'профессор', 'ст.препод.', 'Зав.кафедрой');
    $degrees = array('Не выбрано', 'к.н.', 'д.н.');
    $share_rates_opt = array('Не выбрано', '0.25', '0.50', '0.75', '1.00');
    $ranks = array('Не выбрано', 'доцент', 'профессор');

	$teacher_id = $_GET['id'];
	$server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

	$mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
	mysqli_query ($mysqli, "SET NAMES `utf8`");

	$teacher_result = $mysqli->query("SELECT *
							FROM `teacher` 
							WHERE `idTeacher` = '" . $teacher_id . "' ");

	$ind_plan_result = $mysqli->query("SELECT *
                 FROM individualplan
                 WHERE `Teacher` = '" . $teacher_id . "'");

	if (!empty($ind_plan_result))
	{
		$ind_plan = $ind_plan_result->fetch_assoc();
		$ind_plan_result->close();
	}

	$mysqli->close();
	$teacher = $teacher_result->fetch_assoc();
	$teacher_result->close();

	$default_position = 0;
	$default_degree = 0;
	$default_share_rates = 0;
	$default_rank = 0;

	for ($i = 0; $i < count($positions); $i++) 
	{ 
		if(mb_stripos($teacher['Position'], $positions[$i]) !== false)
			$default_position = $i;
	}

	for ($i = 0; $i < count($degrees); $i++) 
	{ 
		if(mb_stripos($teacher['Degree'], $degrees[$i]) !== false)
			$default_degree = $i;
	}

	for ($i = 0; $i < count($share_rates_opt); $i++) 
	{ 
		if(mb_stripos($teacher['ShareRates'], $share_rates_opt[$i]) !== false)
			$default_share_rates = $i;
	}

	for ($i = 0; $i < count($ranks); $i++) 
	{ 
		if(mb_stripos($teacher['Rank'], $ranks[$i]) !== false)
			$default_rank = $i;
	}

	if($is_denied)
    {
        $readonly = 'readonly'; 
        $style = 'border: 0px;';
    }
    else
    {
        $readonly = '';
        $style = '';
    }

	$form = array();

	$form['personal_data'] = array(
		'#type' => 'fieldset',
        '#collapsible' => TRUE, 
		'#collapsed' => TRUE, 
        '#title' => 'Личные данные',
		);

	if(!$is_denied)
	{
		$form['personal_data']['passport'] = array(
        '#type' => 'fieldset', 
        '#collapsible' => TRUE, 
		'#collapsed' => FALSE, 
        '#title' => 'Паспортные данные', 
        '#prefix' => '<div class="container-inline">',
        '#suffix' => '</div>',
    	);

	    $form['personal_data']['passport']['number'] = array(
	        '#type' => 'textfield', 
	        '#title' => 'Серия', 
	        '#maxlength' => 4,
	        '#size' => 4,
	        '#default_value' =>  substr($teacher['Passport'], 0, 4),
	        '#attributes' => array(
            	'readonly' => array('readonly'),
            	'style' => array('border: 0px;')),
	    	);

	    $form['personal_data']['passport']['series'] = array(
	        '#type' => 'textfield', 
	        '#title' => 'Номер', 
	        '#maxlength' => 6,
	        '#size' => 6,
	        '#default_value' =>  substr($teacher['Passport'], 4),
	        '#attributes' => array(
            	'readonly' => array('readonly'),
            	'style' => array('border: 0px;')),
	    	);
	}
	

    $form['personal_data']['column_left'] = array(
		'#type' => 'container',
		'#attributes' => array(
			'class' => array('column-left'),
			'style' => array('float: left'),
			),
		);

    $form['personal_data']['column_left']['surname'] = array(
        '#type' => 'textfield', 
		'#title' => t('Фамилия'), 
		'#size' => 30,
		'#maxlength' => 45,
		'#attributes' => array(
            $readonly => array($readonly),
            'style' => array($style)),
		'#default_value' => $teacher['Surname'], 
    	);

    $form['personal_data']['column_left']['first_name'] = array(
        '#type' => 'textfield', 
		'#title' => t('Имя'), 
		'#size' => 30,
		'#maxlength' => 20,
		'#attributes' => array(
            $readonly => array($readonly),
            'style' => array($style)),
		'#default_value' => $teacher['FirstName'], 
    	);

    $form['personal_data']['column_left']['patronymic'] = array(
        '#type' => 'textfield', 
		'#title' => t('Отчество'), 
		'#size' => 30,
		'#maxlength' => 45,
		'#attributes' => array(
            $readonly => array($readonly),
            'style' => array($style)),
		'#default_value' => $teacher['Patronymic'], 
    	);

	if(!$is_denied)
	{
		$form['personal_data']['column_left']['initials'] = array(
	        '#type' => 'textfield', 
			'#title' => t('Инициалы'), 
			'#size' => 30,
			'#maxlength' => 20,
			'#default_value' => $teacher['Initials'], 
	    	);

		$form['personal_data']['column_left']['position'] = array(
			'#type' => 'select', 
			'#title' => t('Должность'), 
			'#options' => $positions,
			'#default_value' => $default_position, 
	    	);

		$form['personal_data']['column_right'] = array(
			'#type' => 'container',
			'#attributes' => array(
				'class' => array('column-right'),
				'style' => array('float: right'),
				),
			);

    	$form['personal_data']['column_right']['degree'] = array(
			'#type' => 'select', 
			'#title' => t('Степень'), 
			'#options' => $degrees,
			'#default_value' => $default_degree, 
	    	);

    	$form['personal_data']['column_right']['rank'] = array(
			'#type' => 'select', 
			'#title' => t('Звание'), 
			'#options' => $ranks,
			'#default_value' => $default_rank, 
	    	);

    	$form['personal_data']['column_right']['share_rates'] = array(
			'#type' => 'select', 
			'#title' => t('Доля ставки'), 
			'#options' => $share_rates_opt,
			'#default_value' => $default_share_rates, 
	    	);

	    $form['personal_data']['column_right']['condition'] = array(
			'#type' => 'textfield', 
			'#title' => t('Состояние'), 
			'#size' => 30,
			'#default_value' => $teacher['Condition'], 
	    	);
	}
	else 
	{
		$form['personal_data']['column_right'] = array(
			'#type' => 'container',
			'#attributes' => array(
				'class' => array('column-right'),
				'style' => array('float: right'),
				),
			);

		$form['personal_data']['column_right']['position'] = array(
			'#type' => 'textfield', 
			'#title' => t('Должность'), 
			'#size' => 30,
			'#attributes' => array(
	            $readonly => array($readonly),
	            'style' => array($style)),
			'#default_value' => $teacher['Position'], 
	    	);

		$form['personal_data']['column_right']['degree'] = array(
			'#type' => 'textfield', 
			'#title' => t('Степень'), 
			'#size' => 30,
			'#attributes' => array(
	            $readonly => array($readonly),
	            'style' => array($style)),
			'#default_value' => $teacher['Degree'], 
	    	);

		$form['personal_data']['column_right']['rank'] = array(
			'#type' => 'textfield', 
			'#title' => t('Звание'), 
			'#size' => 30,
			'#attributes' => array(
	            $readonly => array($readonly),
	            'style' => array($style)),
			'#default_value' => $teacher['Rank'], 
	    	);
	}
	    

	if(!$is_denied)
	{
		$form['personal_data']['contact_data'] = array(
	       	'#type' => 'fieldset', 
	        '#collapsible' => TRUE, 
			'#collapsed' => FALSE, 
	        '#title' => 'Контакты', 
	        '#attributes' => array(
				'style' => array('clear: left;'),
				),
       	);

	    $form['personal_data']['contact_data']['column_left'] = array(
			'#type' => 'container',
			'#attributes' => array(
				'class' => array('column-left'),
				'style' => array('float: left'),
				),
			);

	    $form['personal_data']['contact_data']['column_left']['mobile'] = array(
	        '#type' => 'textfield', 
			'#title' => t('Мобильный телефон'),
			'#size' => 30, 
			'#default_value' => $teacher['Mobile'], 
			'#attributes' => array(
            	'readonly' => array('readonly'),
            	'style' => array('border: 0px;')),
				//'#field_prefix' => t('+7'),
	    	);

	    $form['personal_data']['contact_data']['column_left']['work_phone'] = array(
	        '#type' => 'textfield', 
			'#title' => t('Рабочий телефон'),
			'#size' => 30, 
			'#default_value' => $teacher['WorkPhone'], 
			'#attributes' => array(
            	'readonly' => array('readonly'),
            	'style' => array('border: 0px;')),
	    	);

	    $form['personal_data']['contact_data']['column_left']['email'] = array(
		        '#type' => 'textfield', 
				'#title' => t('E-mail'), 
				'#size' => 30,
				'#default_value' => $teacher['E-mail'],
				'#maxlength' => 45,
		    	);


	    $form['personal_data']['contact_data']['column_right'] = array(
			'#type' => 'container',
			'#attributes' => array(
				'class' => array('column-right'),
				'style' => array('float: right'),
				),
			);

	    $form['personal_data']['contact_data']['column_right']['home_phone'] = array(
	        '#type' => 'textfield', 
			'#title' => t('Домашний телефон'), 
			'#size' => 30,
			'#default_value' => $teacher['HomePhone'],
			'#attributes' => array(
            	'readonly' => array('readonly'),
            	'style' => array('border: 0px;')), 
	    	);

		 $form['personal_data']['contact_data']['column_right']['address'] = array(
	        '#type' => 'textfield', 
			'#title' => t('Адрес'), 
			'#size' => 30,
			'#default_value' => $teacher['Address'], 
			'#attributes' => array(
            	'readonly' => array('readonly'),
            	'style' => array('border: 0px;')),
	    	);

		$form['personal_data']['contract'] = array(
	        '#type' => 'fieldset', 
	        '#collapsible' => TRUE, 
			'#collapsed' => FALSE, 
	        '#title' => 'Договор/контракт', 
	    	);

		 $form['personal_data']['contract']['column_left'] = array(
			'#type' => 'container',
			'#attributes' => array(
				'class' => array('column-left'),
				'style' => array('float: left'),
				),
			);

		$form['personal_data']['contract']['column_left']['type'] = array(
	        '#type' => 'textfield', 
			'#title' => t('Вид договора'), 
			'#size' => 30,
			'#default_value' => $teacher['Contract'],  
	    	);

		$effective_contract = $teacher["EffectiveContract"] ? 1 : 0;

	    $form['personal_data']['contract']['column_left']['effective_contract'] = array(
			'#type' => 'select', 
			'#title' => t('Эффективный контракт'), 
			'#options' => array('Нет', 'Да'),
			'#default_value' => $effective_contract, 
	    	);


		$form['personal_data']['contract']['column_right'] = array(
			'#type' => 'container',
			'#attributes' => array(
				'class' => array('column-right'),
				'style' => array('float: right'),
				),
			);

		$form['personal_data']['contract']['column_right']['conclusion_date'] = array(
	        '#type' => 'date',  
			'#title' => t('Дата заключения договора'), 
			'#default_value' => array('year' => intval(substr($teacher['ConclusionDate'], 0, 4)),
				 'month' => intval(substr($teacher['ConclusionDate'], 5, 2)),
				  'day' => intval(substr($teacher['ConclusionDate'], 8, 2))),  
	    	);

		$form['personal_data']['contract']['column_right']['termination_date'] = array(
	        '#type' => 'date',  
			'#title' => t('Дата окончания договора'), 
			'#default_value' => array('year' => intval(substr($teacher['TerminationDate'], 0, 4)),
				 'month' => intval(substr($teacher['TerminationDate'], 5, 2)),
				  'day' => intval(substr($teacher['TerminationDate'], 8, 2))),  
	    	);

		$form['personal_data']['extra'] = array(
	        '#type' => 'fieldset', 
	        '#collapsible' => TRUE, 
			'#collapsed' => FALSE, 
	        '#title' => 'Дополнительно', 
	    	);

		$form['personal_data']['extra']['birth_date'] = array(
	        '#type' => 'date',  
			'#title' => t('Дата рождения'), 
			'#default_value' => array('year' => intval(substr($teacher['BirthDate'], 0, 4)),
				 'month' => intval(substr($teacher['BirthDate'], 5, 2)),
				  'day' => intval(substr($teacher['BirthDate'], 8, 2))),  
	    	);

		if(isset($ind_plan))
		{
			$form['personal_data']['extra']['individual_plan'] = array(
				'#prefix' => '<h3>Индивидуальный план</h3>',
	        	'#markup' => "<table>
						<tr><td>Оригинал ИП." . pathinfo($ind_plan["WordFile"], PATHINFO_EXTENSION) . "</td>
							<td><a href='" . file_create_url($ind_plan["WordFile"]) . "' download><img src = '/sites/all/pic/download.png'></a>
							<a href=http://docs.google.com/viewer?url=" . file_create_url($ind_plan["WordFile"]) . "  title='просмотр'><img src = '/sites/all/pic/preview.png'></a>
							</td>
						</tr>
						<tr><td>Копия для редактирования ИП." . pathinfo($ind_plan["WordCopy"], PATHINFO_EXTENSION) . "</td>
							<td><a href='" . file_create_url($ind_plan["WordCopy"]) . "' download><img src = '/sites/all/pic/download.png'></a>
							<a href=http://docs.google.com/viewer?url=" . file_create_url($ind_plan["WordCopy"]) . "  title='просмотр'><img src = '/sites/all/pic/preview.png'></a>
							</td>
						</tr>
						<tr><td>Оригинал ИП." . pathinfo($ind_plan["ExcelFile"], PATHINFO_EXTENSION) . "</td>
							<td><a href='" . file_create_url($ind_plan["ExcelFile"]) . "' download><img src = '/sites/all/pic/download.png'></a>
							<a href=http://docs.google.com/viewer?url=" . file_create_url($ind_plan["ExcelFile"]) . "  title='просмотр'><img src = '/sites/all/pic/preview.png'></a>
							</td>
						</tr>
						<tr><td>Копия для редактирования ИП." . pathinfo($ind_plan["ExcelCopy"], PATHINFO_EXTENSION) . "</td>
							<td><a href='" . file_create_url($ind_plan["ExcelCopy"]) . "' download><img src = '/sites/all/pic/download.png'></a>
							<a href=http://docs.google.com/viewer?url=" . file_create_url($ind_plan["ExcelCopy"]) . "  title='просмотр'><img src = '/sites/all/pic/preview.png'></a>
							</td>
						</tr>
					</table>"
	    	);
		}

		$form['personal_data']['extra']['notes'] = array(
			'#type' => 'textarea', 
	  		'#title' => t('Заметки'), 
	  		'#default_value' => $teacher['Notes'],
			);

		$form['personal_data']['extra']['submit'] = array(
			'#type' => 'submit',
			'#value' => 'Сохранить'
			);
	}

	return $form;
}

function teachers_view_form_submit($form, &$form_state)
{
	$teacher_id = $_GET['id'];
	$server = 'localhost';
    $username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
  
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $position = $form_state['values']['position'] == 0 ? '' : $form['personal_data']['column_left']['position']['#options'][$form_state['values']['position']];
    $share_rates = $form_state['values']['share_rates'] == 0 ? '' : $form['personal_data']['column_right']['share_rates']['#options'][$form_state['values']['share_rates']];
    $degree = $form_state['values']['degree'] == 0 ? '' : $form['personal_data']['column_right']['degree']['#options'][$form_state['values']['degree']];
    $rank = $form_state['values']['rank'] == 0 ? '' : $form['personal_data']['column_right']['rank']['#options'][$form_state['values']['rank']];

    $is_success = mysqli_query($mysqli, "UPDATE teacher
    						SET
    						`Surname` = '" . $form_state['values']['surname'] . "', 
    						`FirstName` = '" . $form_state['values']['first_name'] . "', 
    						`Patronymic` = '" . $form_state['values']['patronymic'] . "', 
    						`Initials` = '" . $form_state['values']['initials'] . "', 
    						`E-mail` = '" . $form_state['values']['email'] . "',
    						`Position` = '" . $position . "',
    						`ShareRates` = '" . $share_rates . "',
    						`Degree` = '" . $degree . "',
    						`Rank` = '" . $rank . "',
    						`Contract` = '" . $form_state['values']['type'] . "',
    						`ConclusionDate` = '" . $form_state['values']['conclusion_date']['year'] . '-' . $form_state['values']['conclusion_date']['month'] . '-' . $form_state['values']['conclusion_date']['day'] . "',
    						`TerminationDate` = '" . $form_state['values']['termination_date']['year'] . '-' . $form_state['values']['termination_date']['month'] . '-' . $form_state['values']['termination_date']['day'] . "',
    						`Condition` = '" . $form_state['values']['condition'] . "',
    						`EffectiveContract` = '" . $form_state['values']['effective_contract'] . "',
    						`Notes` = '" . $form_state['values']['notes'] . "'
    						WHERE `idTeacher` = '" . $teacher_id . "'
    						");
    $mysqli->close();

 /*   $c_user=user_load($user->uid);
	$c_user->mail = $form_state['values']['Email'];
	user_save($c_user);*/
	if($is_success)
		drupal_set_message("Данные успешно изменены!");
	else
		drupal_set_message("Произошла ошибка при сохранении данных", "error");
}
