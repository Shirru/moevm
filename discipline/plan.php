<?php

//$path = drupal_get_path('module', 'discipline');
    //drupal_add_js($path .'/xlsx.core.min.js');
/**
 * Загрузка группы
 */
function discipline_plan_page()
{
    global $user;
    $is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);
    $is_denied = ($is_teacher || $is_student) && !$is_educational;

    $server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';
    $output = "";

    $rows = array();

    if($is_denied)
    {
        $output .= "<h3>УП бакалавров</h3>";
        $header = array('', 'Номер', 'Код направления', 'Название направления', 'Кафедра');
    }
    else
    {
        $output .= "<h3>УП бакалавров</h3><div style='text-align: right'>
        <a href='plan/add?stage=1'  title='Добавить УП'> Добавить </a>
        </div>";
        $header = array('', 'Номер', 'Код направления', 'Название направления', 'Кафедра', '');
    }

    
    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");
    $bachelor_result = $mysqli->query("SELECT `idCurriculum`, `CurriculumNum`, 
                  `Direction`, `Chair`
                  FROM curriculum
                  WHERE (`Stage` = '1' AND `Chair` = 
                  (SELECT `idChair` FROM `chair`
                  WHERE `ChairNum` = 14))");

    foreach($bachelor_result as $row) 
    {
        $direction_result = $mysqli->query("SELECT `DirectionCode`, `DirectionName`
                  FROM direction
                  WHERE `idDirection` = '" . $row["Direction"] . "'");
        $direction_row = $direction_result->fetch_row();
        $direction_code = $direction_row[0];
        $direction_name = $direction_row[1];
        $direction_result->close();

        $chair_result = $mysqli->query("SELECT `ChairShortName`
                 FROM chair
                 WHERE `idChair` = '" . $row["Chair"] . "'");
        $chair_row = $chair_result->fetch_row();
        $chair_id = $chair_row[0];
        $chair_result->close();

        if($is_denied)
        {
            $rows[] = array("<a href='plan/view?id=".$row ["idCurriculum"]."'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
            $row["CurriculumNum"], $direction_code, $direction_name, $chair_id,);
        }
        else
        {
            $rows[] = array("<a href='plan/view?id=".$row ["idCurriculum"]."'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
            $row["CurriculumNum"], $direction_code, $direction_name, $chair_id,
            "<a href='#' onclick='if(confirm(\"Вы действительно хотите удалить УП?\")){parent.location = \"/disciplines/del?cur_id=" . $row ["idCurriculum"] . "&curriculum=true\";}else return false;'  title='удалить'><img src='/sites/all/pic/delete.png'></a>");
        }
       
    }
    $bachelor_result->close();
    $table = theme('table', array('header' => $header, 'rows' => $rows));

    $output .= $table;

    if($is_denied)
    {
        $output .= "<h3>УП магистров</h3>";
    }
    else
    {
        $output .= "<h3>УП магистров</h3><div style='text-align: right'>
        <a href='plan/add?stage=2'  title='Добавить УП'> Добавить </a>
        </div>";
    }

    $master_result = $mysqli->query("SELECT `idCurriculum`, `CurriculumNum`, 
                  `Direction`, `Chair`
                  FROM curriculum
                  WHERE (`Stage` = '2' AND `Chair` = 
                  (SELECT `idChair` FROM `chair`
                  WHERE `ChairNum` = 14))");

    $rows_master = array();
    foreach($master_result as $row) 
    {
        $direction_result = $mysqli->query("SELECT `DirectionCode`, `DirectionName`
                  FROM direction
                  WHERE `idDirection` = '" . $row["Direction"] . "'");
        $direction_row = $direction_result->fetch_row();
        $direction_code = $direction_row[0];
        $direction_name = $direction_row[1];
        $direction_result->close();

        $chair_result = $mysqli->query("SELECT `ChairShortName`
                 FROM chair
                 WHERE `idChair` = '" . $row["Chair"] . "'");
        $chair_row = $chair_result->fetch_row();
        $chair_id = $chair_row[0];
        $chair_result->close();

        if($is_denied)
        {
            $rows_master[] = array("<a href='plan/view?id=".$row ["idCurriculum"]."'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
            $row["CurriculumNum"], $direction_code, $direction_name, $chair_id,);
        }
        else
        {
            $rows_master[] = array("<a href='plan/view?id=".$row ["idCurriculum"]."'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
            $row["CurriculumNum"], $direction_code, $direction_name, $chair_id,
            "<a href='#' onclick='if(confirm(\"Вы действительно хотите удалить УП?\")){parent.location = \"/disciplines/del?cur_id=" . $row ["idCurriculum"] . "&curriculum=true\";}else return false;'  title='удалить'><img src='/sites/all/pic/delete.png'></a>");
        }
       
    }

    $master_result->close();
    $mysqli->close();
    $table_master = theme('table', array('header' => $header, 'rows' => $rows_master));

    $output .=  $table_master;
    return $output;  
 // return drupal_get_form ( 'discipline_plan_page_form' );
}

function discipline_plan_view_page()
{
  return drupal_get_form ( 'discipline_plan_view_page_form' );
}

function discipline_plan_view_page_form($form, &$form_state)
{
    global $user;
    $is_teacher = array_search('teacher', $user->roles);
    $is_student = array_search('student', $user->roles);
    $is_educational = array_search('educational and methodical', $user->roles);
    $is_denied = ($is_teacher || $is_student) && !$is_educational;

    $curriculum_id = $_GET['id'];
    $server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

    $output = '';
    $curriculum_nums = array();

    if($is_denied) 
    {
        $header = array('', 'Индекс', 'Дисциплина', '<abbr title="Экзамен">Экз</abbr>', '<abbr title="Зачет">За</abbr>',
        '<abbr title="Зачет с оценкой">ЗаО</abbr>', '<abbr title="Лекции">Лек</abbr>', '<abbr title="Лабораторные работы">Лаб</abbr>', 
	'<abbr title="Практические занятия">Практ</abbr>', '<abbr title="Самостоятельная работа студентов">СРС</abbr>',
	'<abbr title="Курсовой проект">КП</abbr>',
        '<abbr title="Курсовая работа">КР</abbr>', '<abbr title="Зачетные единицы времени в часах (единица ЗЕТ равна 36
академическим часам)">ЗЕТ</abbr>', 'Всего',
	'<abbr title="Семестр">Сем</abbr>');
    }
    else
    {
        $header = array('', 'Индекс', 'Дисциплина', '<abbr title="Экзамен">Экз</abbr>', '<abbr title="Зачет">За</abbr>',
        '<abbr title="Зачет с оценкой">ЗаО</abbr>', '<abbr title="Лекции">Лек</abbr>', '<abbr title="Лабораторные работы">Лаб</abbr>', 
	'<abbr title="Практические занятия">Практ</abbr>', '<abbr title="Самостоятельная работа студентов">СРС</abbr>',
	'<abbr title="Курсовой проект">КП</abbr>',
        '<abbr title="Курсовая работа">КР</abbr>', '<abbr title="Зачетные единицы времени в часах (единица ЗЕТ равна 36
академическим часам)">ЗЕТ</abbr>', 'Всего',
	'<abbr title="Семестр">Сем</abbr>', '');
    }

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $curriculum_result = $mysqli->query("SELECT `CurriculumNum`, 
                  `Direction`, `Chair`
                 FROM curriculum
                 WHERE `idCurriculum` = '" . $curriculum_id . "'");

    foreach($curriculum_result as $row) 
    {
        $curriculum_num = $row["CurriculumNum"]; 
        $direction = $row["Direction"];
    }
    $curriculum_result->close();

    $cur_discipline_result = $mysqli->query("SELECT *
                 FROM curriculumdiscipline 
                 WHERE `curriculum` = '" . $curriculum_id . "' ORDER BY `Semester`");

    $rows = array();

    foreach($cur_discipline_result as $row) 
    {
        $discipline_result = $mysqli->query("SELECT `DisFullName`
                 FROM discipline
                 WHERE `idDiscipline` ='" . $row["Discipline"]  . "' ORDER BY `DisFullName`");
        $discipline_name = $discipline_result->fetch_row();
        $discipline_result->close();

        if($is_denied) 
        {
            $rows[] = array("<a href='discipline?id=".$row ["idCurriculumDiscipline"]."'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
            $row["DisIndex"], $discipline_name[0], $row["Exam"],
            $row["CreditW/OGrade"], $row["CreditWithGrade"],
            $row["Lecture"], $row["Lab"], $row["Practice"],
            $row['Solo'],
            $row["CourseProject"], $row["CourseWork"],
            $row['Zet'], $row['Total'],
            $row["Semester"],
           );
        }
        else
        {
            $rows[] = array("<a href='discipline?id=".$row ["idCurriculumDiscipline"]."'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
            $row["DisIndex"], $discipline_name[0], $row["Exam"],
            $row["CreditW/OGrade"], $row["CreditWithGrade"],
            $row["Lecture"], $row["Lab"], $row["Practice"],
            $row['Solo'],
            $row["CourseProject"], $row["CourseWork"],
            $row['Zet'], $row['Total'],
            $row["Semester"],
            "<a href='#' onclick='if(confirm(\"Вы действительно хотите удалить дисциплину УП?\")){parent.location = \"/disciplines/del?dis_id=" . $row["idCurriculumDiscipline"] . "&cur_id=" . $curriculum_id . "\";}else return false;'  title='удалить'><img src='/sites/all/pic/delete.png'></a>");
        }

    }
    $cur_discipline_result->close();

    $direction_result = $mysqli->query("SELECT `idDirection`, `DirectionCode`,`DirectionName`
                 FROM `direction`
                 WHERE `idDirection` = '" . $direction . "'");

    foreach ($direction_result as $row) 
    {
       $direction_name = $row['DirectionName'];
       $direction_code = $row['DirectionCode'];
       $direction_id = $row['idDirection'];
    }
    $direction_result->close();

    $mysqli->close();
    $table = theme('table', array('header' => $header, 'rows' => $rows));

    if($is_denied)
    {
        $readonly = 'readonly'; 
    }
    else
    {
        $readonly = '';
    }

    $form = array();  

	/*$form['info'] = array(
	    '#prefix' => "<div style='text-align: right'>",
            '#markup' => "ИЗ – индивидуальное задание.<br>
К – контрольная работа.<br>
КР – курсовая работа",
	    '#suffix' => '</div>',
            );*/

    $form_state['storage']['direction_id'] = $direction_id;

    $form['curriculum_num'] = array(
        '#title' => t('Номер УП'),
        '#type' => 'textfield', 
        '#default_value' => $curriculum_num,
        '#attributes' => array(
            $readonly => array($readonly),),
        );

    $form['direction_code'] = array(
        '#title' => t('Код направления'),
        '#type' => 'textfield', 
        '#default_value' => $direction_code,
        '#attributes' => array(
            $readonly => array($readonly),),
        );

    $form['direction_name'] = array(
        '#title' => t('Название направления'),
        '#type' => 'textfield', 
        '#default_value' => $direction_name,
        '#attributes' => array(
            $readonly => array($readonly),),
        );

    if(!$is_denied)
    {
        $form['submit'] = array(
            '#type' => 'submit',
            '#value' => t('Сохранить'),
            );
    }
   
    if($is_denied)
    {
        $prefix = "<br><h3>Список дисциплин</h3>";
    }
    else
    {
        $prefix = "<br><h3>Список дисциплин</h3><div style='text-align: right'>
        <a href='load_disciplines?id=".$curriculum_id."'  title='Загрузить дисциплины из файла'> Загрузить </a>
        <a href='add_discipline?id=" . $curriculum_id . "'  title='Добавить новую дисциплину'> Добавить </a> 
        </div>";
    }

    $form['curriculum_table'] = array(
        '#prefix' => $prefix,
        '#suffix' => '</div>',
        '#markup' => $table,  
        );

    return $form;
}

function discipline_plan_view_page_form_submit($form, &$form_state)
{
    $curriculum_id = $_GET['id'];
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb';

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $is_success_cur = mysqli_query ($mysqli, "UPDATE `curriculum` SET 
        `CurriculumNum` = '" . $form_state['values']['curriculum_num'] . "'
        WHERE `idCurriculum` = '" . $curriculum_id . "'");

    $is_success_dir = mysqli_query ($mysqli, "UPDATE `direction` SET 
        `DirectionCode` = '" . $form_state['values']['direction_code'] . "',
        `DirectionName` = '" . $form_state['values']['direction_name'] ."'
        WHERE `idDirection` = '" . $form_state['storage']['direction_id'] . "'");

    if($is_success_dir && $is_success_cur)
        drupal_set_message('Данные обновлены успешно!');
    else
        drupal_set_message('Произошла ошибка при сохранении данных', 'error');
}

function discipline_plan_load_disciplines_page()
{
    return drupal_get_form ( 'discipline_plan_load_disciplines_page_form' );
}

function disciplines_plan_load_multistep_form($form, &$form_state)
{
    // Обёртка для формы. Каждый раз в неё будет передаваться новая форма через ajax.
    $form['#prefix'] = '<div id="disciplines-plan-load-multistep-form-wrapper">';
    $form['#suffix'] = '</div>';
    
    // Данные в форме будут представлены в виде дерева, т.е. 
    // сохранять ключи родительских элементов.
    $form['#tree'] = TRUE;

    // Если форма только что была создана, то мы окажемся на первом шаге.
    // Если же пользователь уже "полазил" по форме, то забираем текущий шаг формы.
    $step = empty($form_state['storage']['step']) ? 1 : $form_state['storage']['step'];
    $form_state['storage']['step'] = $step;

    // Смотрим, на каком шаге мы сейчас находимся, и в зависимости
    // от этого показываем или скрываем часть формы.
    switch ($step) 
    {
        case 1:
          // Если пользователь находится на первом шаге,
          // то показываем ему форму для первого шага.
          $form['step1'] = array(
              '#type' => 'fieldset', 
              '#title' => 'Выберите файл',
          );

          $form['step1']['file'] = array(
              '#type' => 'file',
          );

        break;

        case 2:
          // Задаём форму для второго шага.
          $form['step2'] = array(
              '#type' => 'fieldset', 
              '#title' => 'Загруженные дисциплины',
          );

          $form['step2']['table'] = array(
            '#prefix' => '<div id="disciplines-plan-table">',
            '#suffix' => '</div>',
            '#markup' => $form_state['storage']['table'], 
          );
        
          break;
    }
    //Кнопки для каждого шага
    
    $form['actions'] = array('#type' => 'actions');
    
    // Если мы на последнем шаге - то показываем кнопки "Отмена" и "Подтвердить".
    if ($step == 2) 
    {
        $form['actions']['submit'] = array(
            '#type' => 'submit', 
            '#value' => 'Подтвердить',
            );

        $form['actions']['cancel'] = array(
            '#type' => 'submit', 
            '#value' => 'Отмена',
            );
    }

    // Если мы не достигли последнего шага, то у нас обязательно
    // будет присутствовать кнопка "Далее".
    if ($step < 2) 
    {
        $form['actions']['next'] = array(
            '#type' => 'submit', 
            '#value' => 'Далее', 
            // На кнопку вешаем ajax-обработчик, который будет возвращать форму
            // в ранее созданный <div id="disciplines-plan-load-multistep-form-wrapper"></div>
            '#ajax' => array(
                'wrapper' => 'disciplines-plan-load-multistep-form-wrapper', 
                'callback' => 'disciplines_plan_load_multistep_ajax_callback',
                ),
            );
    }

    // Если мы ушли с первого шага, то покажем кнопку "Назад".
    if ($step > 1) 
    {
        $form['actions']['prev'] = array(
            '#type' => 'submit', 
            '#value' => 'Назад',    
            // Это хороший трюк - не валидируем форму, если нажимаем кнопку "Предыдущий шаг".    
            '#limit_validation_errors' => array(),
            '#submit' => array('disciplines_plan_load_multistep_form_submit'), 
            '#ajax' => array(
                'wrapper' => 'disciplines-plan-load-multistep-form-wrapper', 
                'callback' => 'disciplines_plan_load_multistep_ajax_callback',
                ),
            );
    }

    return $form;
}

function disciplines_plan_load_multistep_ajax_callback($form, $form_state) {
    // Указываем, что хотим перезагрузить всю форму, 
    // просто вернув её целиком обратно.
    return $form;
}

function disciplines_plan_load_multistep_form_validate($form, &$form_state) 
{
  //если была нажата кнопка "Далее" на первом шаге, то загружаем файл
    if (isset($form['actions']['next']['#value']) && $form_state['triggering_element']['#value'] == $form['actions']['next']['#value']) 
    {
        $validators = array(
            'file_validate_extensions' => array('xlsx xls xlsm'), // Проверка на расширения
        );

        if ($file = file_save_upload('step1', $validators, 'public://documents/')) 
        {
            $form_state['values']['step1']['file'] = $file;
		//debug($file); 
        }
        else 
        {
            form_set_error('file', 'Файл не был загружен');
        }
    }
}

function disciplines_plan_load_multistep_form_submit($form, &$form_state) 
{
    // Сохраняем состояние формы, полученное при переходе на новый шаг.
    $current_step = 'step' . $form_state['storage']['step'];
    if (!empty($form_state['values'][$current_step])) 
    {
        $form_state['storage']['values'][$current_step] = $form_state['values'][$current_step];
    }

    if (isset($_GET['id'])) $curriculum_id = $_GET['id'];

    // Если перешли на следующий шаг - то увеличиваем счётчик шагов.
    if (isset($form['actions']['next']['#value']) && $form_state['triggering_element']['#value'] == $form['actions']['next']['#value']) 
    {
        $form_state['storage']['step']++;

        $file = $form_state['values']['step1']['file'];

        // подключаем библиотеку для работы с файлом
        require_once drupal_get_path('module', 'discipline') . "/Classes/PHPExcel.php";
        $path = 'public://documents/';
        $path = drupal_realpath($path);

        $excel = PHPExcel_IOFactory::load($path . "/" . $file->filename);
        $excel->setActiveSheetIndexByName("Шахм");
        $current_sheet = $excel->getActiveSheet();
        $stage_name = $current_sheet->getCellByColumnAndRow(0, 1)->getValue();

        if(mb_stripos($stage_name, "бакалавры") !== false)
        {
            $stage = 1;
        }
        if(mb_stripos($stage_name, "магистры") !== false)
        {
            $stage = 2;
        }

        //функция парсит файл и возвращает список дисциплин
        if($stage == 1)
        {
            $discipline_list = discipline_plan_view_page_parse_curriculum($excel);
        }
        else if ($stage == 2)
        {
            $discipline_list = discipline_plan_view_page_parse_master_curriculum($excel);
        }

        //формируем таблицу из полученного результата
        $header = array('Индекс', 'Дисциплина', 'Сем', 'Контроль', 'Всего',
         'Лек', 'Лаб', 'Практ', 'СРС', 'ЗЕТ', 'Каф');

        $table = theme('table', array('header' => $header, 'rows' => $discipline_list));

        file_delete($file); 

        $form_state['storage']['table'] = $table;
        $form_state['storage']['discipline_list'] = $discipline_list;

        $step_name = 'step' . $form_state['storage']['step'];

        if (!empty($form_state['storage']['values'][$step_name])) 
        {
            $form_state['values'][$step_name] = $form_state['storage']['values'][$step_name];
        }
    }
    
    // Если вернулись на шаг назад - уменьшаем счётчик шагов.
    if (isset($form['actions']['prev']['#value']) && $form_state['triggering_element']['#value'] == $form['actions']['prev']['#value']) 
    {
        $form_state['storage']['step']--;
      
        // Забираем из хранилища данные по предыдущему шагу и возвращаем их в форму.
        $step_name = 'step' . $form_state['storage']['step'];
        $form_state['values'][$step_name] = $form_state['storage']['values'][$step_name];
    }

    // Если была нажата кнопка "Отмена", возвращаемся к УП.
    if (isset($form['actions']['cancel']['#value']) && $form_state['triggering_element']['#value'] == $form['actions']['cancel']['#value']) 
    {
        drupal_goto("/disciplines/plan/view", array('query' => array("id" => $curriculum_id)));
    }

    // Если пользователь прошёл все шаги и нажал на кнопку "Принять",
    // то обрабатываем полученные данные со всех шагов.
    if (isset($form['actions']['submit']['#value']) && $form_state['triggering_element']['#value'] == $form['actions']['submit']['#value']) 
    {
        // заносим данные в базу данных
        $table = $form_state['storage']['discipline_list'];
        discipline_plan_load_disciplines_save($curriculum_id, $table);

        $form_state['rebuild'] = FALSE;
        drupal_goto("/disciplines/plan/view", array('query' => array("id" => $curriculum_id)));
    }

    // Указываем, что форма должна быть построена заново.
    $form_state['rebuild'] = TRUE;
}

//на вход подается загруженная книга excel
function discipline_plan_view_page_parse_curriculum($curriculum)
{
    $nums_of_columns = array();
    require_once drupal_get_path('module', 'discipline') . "/Classes/PHPExcel.php";

    $curriculum->setActiveSheetIndexByName("Курс1");

    $current_course_sheet = $curriculum->getActiveSheet();
    foreach( $current_course_sheet->getRowIterator() as $row ) 
    {
        if(count($nums_of_columns) == 16) break;
        foreach( $row->getCellIterator() as $cell ) 
        {
            if(count($nums_of_columns) == 16) break;
            $value = $cell->getValue();
            switch ($value) {
                case '№':
                    $nums_of_columns["Number"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'Индекс':
                    $nums_of_columns["Index"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'Наименование':
                    $nums_of_columns["Dis_name"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'Семестр 1':
                    $nums_of_columns["Sem1"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'Семестр 2':
                    $nums_of_columns["Sem2"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'Лек':
                    if(PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1 < $nums_of_columns["Sem2"])
                      $nums_of_columns["Lec1"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    else
                      $nums_of_columns["Lec2"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'Лаб':
                    if(PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1 < $nums_of_columns["Sem2"])
                      $nums_of_columns["Lab1"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    else
                      $nums_of_columns["Lab2"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'Пр':
                    if(PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1 < $nums_of_columns["Sem2"])
                      $nums_of_columns["Pract1"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    else
                      $nums_of_columns["Pract2"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'СРС':
                    if(PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1 < $nums_of_columns["Sem2"])
                      $nums_of_columns["Solo1"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    else
                      $nums_of_columns["Solo2"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'ЗЕТ':
                    if(PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1 < $nums_of_columns["Sem2"])
                      $nums_of_columns["Zet1"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    else
                      $nums_of_columns["Zet2"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'Каф.':
                    $nums_of_columns["Chair"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
            }
        }
    }
    $discipline_list = array();

    $last_row = 0;
    for($i = 1; $i < 5; $i++)
    {
        $curriculum->setActiveSheetIndexByName("Курс" . $i);
        $current_course_sheet = $curriculum->getActiveSheet();

        $highest_row = $current_course_sheet->getHighestRow();
        for ($row = $last_row + 1; $row <= $highest_row; $row++)
        {
            $cell = $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Number"], $row);
            $value = $cell->getValue();
            while(is_numeric($value))
            {
                $cell = $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Dis_name"], $row);
                $value = $cell->getValue();

                if($current_course_sheet->getCellByColumnAndRow($nums_of_columns["Lec1"], $row)->getValue() || 
                $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Lab1"], $row)->getValue() || 
                $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Pract1"], $row)->getValue())
                {
                    $discipline_list[] = array(
                    "Index" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Index"], $row)->getValue(),
                    "Dis_name" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Dis_name"], $row)->getValue(),
                    "Sem" => $i * 2 - 1,
                    "Control" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Sem1"], $row)->getValue(),
                    "Total" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Sem1"] + 1, $row)->getValue(),
                    "Lec" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Lec1"], $row)->getValue(),
                    "Lab" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Lab1"], $row)->getValue(),
                    "Pract" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Pract1"], $row)->getValue(),
                    "Solo" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Solo1"], $row)->getValue(),
                    "Zet" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Zet1"], $row)->getValue(),
                    "Chair" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Chair"], $row)->getValue());
                }

                if($current_course_sheet->getCellByColumnAndRow($nums_of_columns["Lec2"], $row)->getValue() || 
                $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Lab2"], $row)->getValue() || 
                $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Pract2"], $row)->getValue())
                {
                    $discipline_list[] = array(
                    "Index" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Index"], $row)->getValue(),
                    "Dis_name" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Dis_name"], $row)->getValue(),
                    "Sem" => $i * 2,
                    "Control" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Sem2"], $row)->getValue(),
                    "Total" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Sem2"] + 1, $row)->getValue(),
                    "Lec" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Lec2"], $row)->getValue(),
                    "Lab" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Lab2"], $row)->getValue(),
                    "Pract" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Pract2"], $row)->getValue(),
                    "Solo" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Solo2"], $row)->getValue(),
                    "Zet" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Zet2"], $row)->getValue(),
                    "Chair" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Chair"], $row)->getValue());
                } 
                
              
                $last_row = $row;
            }
         }
    }
   return $discipline_list;  
}

// функция сохраняет список дисциплин в базу данных
function discipline_plan_load_disciplines_save($curriculum_id, $table)
{
    $server = 'localhost';
	$username = 'moevm_user';
	$password = 'Pwt258E6JT8QAz3y';
	$database = 'moevmdb';

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    mysqli_query ($mysqli, "DELETE FROM `curriculumdiscipline` 
        WHERE `idCurriculum` = '" . $curriculum_id . "'");

    for($i = 0; $i < count($table); $i++) 
    {
        $is_discipline_result = $mysqli->query("SELECT `idDiscipline`
                 FROM discipline
                 WHERE `DisFullName` = '" . $table[$i]["Dis_name"] . "'");
        if($dis_row = $is_discipline_result->fetch_row())
        {
            $discipline_id = $dis_row[0];
            $is_discipline_result->close();
        }
        else
        {
            $is_chair_result = $mysqli->query("SELECT `idChair`
                 FROM chair
                 WHERE `ChairNum` = '" . $table[$i]["Chair"] . "'");
            if($chair_row = $is_chair_result->fetch_row())
            {
                $chair_id = $chair_row[0];
                $is_chair_result->close();
            }
           /* else
            {

            }*/

            $discipline_result = $mysqli->query("INSERT INTO discipline
                (`DisFullName`, `Chair`)
                VALUES ('" . $table[$i]["Dis_name"] . "', '" . $chair_id . "')");

            $is_discipline_result = $mysqli->query("SELECT `idDiscipline`
                 FROM discipline
                 WHERE `DisFullName` = '" . $table[$i]["Dis_name"] . "'");
            $dis_row = $is_discipline_result->fetch_row();
            $discipline_id = $dis_row[0];
            $is_discipline_result->close();
        }

        $control = discipline_plan_load_disciplines_parse_control($table[$i]["Control"]);
        // после того как получили id дисциплины и кафедры, можем наконец-то вставить результат
        mysqli_query ($mysqli, "INSERT INTO curriculumdiscipline
                (`DisIndex`,
                `Discipline`,
                `Curriculum`,
                `Exam`,
                `CreditW/OGrade`, 
                `CreditWithGrade`,
                `Lecture`,
                `Lab`,
                `Practice`,
                `Solo`,
                `CourseProject`,
                `CourseWork`,
                `Zet`,
                `Total`,
                `Semester`)
              VALUES ('". $table[$i]["Index"] ."',
                '".  $discipline_id ."',
                '". $curriculum_id ."',
                '". $control["Exam"] ."',
                '". $control["CreditW/OGrade"] ."',
                '". $control["CreditWithGrade"] ."',
                '". $table[$i]["Lec"] ."',
                '". $table[$i]["Lab"] ."',
                '". $table[$i]["Pract"] ."',
                '". $table[$i]["Solo"] ."',
                '". $control["CourseProject"] ."',
                '". $control["CourseWork"] ."',
                '". $table[$i]["Zet"] ."',
                '". $table[$i]["Total"] ."',
                '". $table[$i]["Sem"] ."')");
    }
    $mysqli->close();

}

function discipline_plan_load_disciplines_parse_control($value)
{
    $control = array(
        "Exam" => 0,
        "CreditW/OGrade" => 0,
        "CreditWithGrade" => 0,
        "CourseProject" => 0,
        "CourseWork" => 0,
        );
    if($pos = stripos($value, "За") !== false)
    {
        if($pos = stripos($value, "ЗаО") !== false)
            $control["CreditWithGrade"] = 1;
        else 
            $control["CreditW/OGrade"] = 1;
    }
    if($pos = stripos($value, "Экз") !== false)
        $control["Exam"] = 1;
    if($pos = stripos($value, "КП") !== false)
        $control["CourseProject"] = 1;
    if($pos = stripos($value, "КР") !== false)
        $control["CourseWork"] = 1;
    return $control;
}

function discipline_plan_add_page()
{
    return drupal_get_form ( 'discipline_plan_add_page_form' );
}

function discipline_plan_add_page_form($form, &$form_state) 
{
    $direction_codes = discipline_plan_add_dir_codes();
    // Обёртка для формы. Каждый раз в неё будет передаваться новая форма через ajax.
    $form['#prefix'] = '<div id="discipline-plan-add-page-form-wrapper">';
    $form['#suffix'] = '</div>';
    
    // Данные в форме будут представлены в виде дерева, т.е. 
    // сохранять ключи родительских элементов.
    $form['#tree'] = TRUE;

    // Если форма только что была создана, то мы окажемся на первом шаге.
    // Если же пользователь уже "полазил" по форме, то забираем текущий шаг формы.
    $step = empty($form_state['storage']['step']) ? 1 : $form_state['storage']['step'];
    $form_state['storage']['step'] = $step;

    switch ($step) 
    {
        case 1:
          // Если пользователь находится на первом шаге,
          // то показываем ему форму для первого шага.
        $form['step1'] = array(
              '#type' => 'fieldset', 
          );

        $form['step1']['load_file_block'] = array(
            '#type' => 'fieldset', 
            '#title' => t('Загрузить из файла'), 
            '#weight' => -5, 
            '#collapsible' => TRUE, 
            '#collapsed' => TRUE,
            );  

        $form['step1']['load_file_block']['load_file'] = array(
            '#type' => 'file', 
            '#title' => t('Выберите файл'),
            );

        $form['step1']['load_file_block']['load_submit'] = array(
            '#type' => 'submit', 
            '#value' => 'Загрузить',
            '#ajax' => array(
                'wrapper' => 'discipline-plan-add-page-form-wrapper', 
                'callback' => 'discipline_plan_add_page_ajax_callback',
                ),
            );

        $form['step1']['curriculum_num'] = array( 
            '#title' => t('Номер УП'), 
            '#type' => 'textfield',
            );

        $form['step1']['direction_block'] = array(
		'#prefix' => '<div id = "direction-div">',
    		'#suffix' => '</div>',
            '#type' => 'fieldset', 
            '#title' => t('Направление'), 
            );

	/*$form['step1']['direction_block']['direction_code'] = array(
            '#title' => t('Код направления'), 
            '#type' => 'textfield',

            );*/

        if (isset($form_state['storage']['is_new_dir']) && $form_state['storage']['is_new_dir'] == 1) {
            $form['step1']['direction_block']['new_direction_code'] = array(
                '#title' => t('Код направления'),
                '#type' => 'textfield',
                );

            $form['step1']['direction_block']['new_direction_name'] = array(
                '#title' => t('Название направления'),
                '#type' => 'textfield',
            );

            $form['step1']['direction_block']['back_new_dir_submit'] = array(
                '#type' => 'submit',
                '#value' => t('Назад'),
                '#ajax' => array(
                    'wrapper' => 'direction-div',
                    'callback' => 'discipline_plan_add_form_ajax_callback',
                ),
            );
        }
        else {
            $form['step1']['direction_block']['direction_code'] = array(
                '#title' => t('Код направления'),
                '#type' => 'select',
                '#options' => array_keys($direction_codes),
                '#default_value' => 0,
                '#ajax' => array(
                    'wrapper' => 'direction-div',
                    'callback' => 'discipline_plan_add_form_ajax_callback',
                ),
              );

            $form['step1']['direction_block']['add_new_dir_submit'] = array(
                '#type' => 'submit',
                '#value' => t('Добавить новое направление'),
                '#attributes' => array('style' => 'position: absolute; right: 10px; bottom: 10px;'),
                '#ajax' => array(
                    'wrapper' => 'direction-div',
                    'callback' => 'discipline_plan_add_form_ajax_callback',
                ),
            );


            $direction_name = isset($form_state['values']['step1']['direction_block']['direction_code']) ?
                $direction_codes[$form['step1']['direction_block']['direction_code']['#options']
                [$form_state['values']['step1']['direction_block']['direction_code']]] :
                $direction_codes[array_keys($direction_codes)[0]];

            $form_state['storage']['direction_name'] = $direction_name;

            $form['step1']['direction_block']['direction_name'] = array(
                '#title' => t('Название направления'),
                '#type' => 'textfield',
                '#default_value' => $direction_name,
                '#attributes' => array(
                    'readonly' => array('readonly'),
                    'style' => array('border: 0px;'),
                ),
            );

        }
        $form['step1']['add_submit'] = array(
        '#type' => 'submit',
        '#value' => t('Добавить'),
        );

        break;

        case 2:
          // Задаём форму для второго шага.
          $form['step2'] = array(
              '#type' => 'fieldset', 
              '#title' => 'Загруженные дисциплины',
          );

          $form['step2']['table'] = array(
            '#prefix' => '<div id="disciplines-plan-table">',
            '#suffix' => '</div>',
            '#markup' => $form_state['storage']['table'], 
          );
        
          break;
    }
    //Кнопки для каждого шага
    
    $form['actions'] = array('#type' => 'actions');
    
    // Если мы на последнем шаге - то показываем кнопки "Отмена" и "Подтвердить".
    if ($step == 2) 
    {
        $form['actions']['submit'] = array(
            '#type' => 'submit', 
            '#value' => 'Подтвердить',
            );

        $form['actions']['cancel'] = array(
            '#type' => 'submit', 
            '#value' => 'Отмена',
            );
    }

    // Если мы ушли с первого шага, то покажем кнопку "Назад".
    if ($step > 1) 
    {
        $form['actions']['prev'] = array(
            '#type' => 'submit', 
            '#value' => 'Назад',    
            // Это хороший трюк - не валидируем форму, если нажимаем кнопку "Предыдущий шаг".    
            '#limit_validation_errors' => array(),
            '#submit' => array('discipline_plan_add_page_form_submit'), 
            '#ajax' => array(
                'wrapper' => 'discipline-plan-add-page-form-wrapper', 
                'callback' => 'discipline_plan_add_page_ajax_callback',
                ),
            );
    }

    return $form;
}

function discipline_plan_add_page_ajax_callback($form, &$form_state) {
    // Указываем, что хотим перезагрузить всю форму, 
    // просто вернув её целиком обратно.
    return $form;
}

function discipline_plan_add_form_ajax_callback($form, &$form_state) {
    $form['step1']['direction_block']['direction_name']['#value'] = $form_state['storage']['direction_name'];

    return $form['step1']['direction_block'];
}

function discipline_plan_add_page_form_validate($form, &$form_state) 
{
   
  //если была нажата кнопка "Загрузить" на первом шаге, то загружаем файл
    if (isset($form['step1']['load_file_block']['load_submit']['#value']) && $form_state['triggering_element']['#value'] == $form['step1']['load_file_block']['load_submit']['#value']) 
    {
        $validators = array(
        'file_validate_extensions' => array('xlsx xls xlsm'), // Проверка на расширения
        );

        if ($file = file_save_upload('step1', $validators, 'public://documents/')) 
        {
            $form_state['values']['step1']['load_file_block']['load_file'] = $file; 
        }
        else 
        {
            form_set_error('file', 'Файл не был загружен');
        }
    }
}

function discipline_plan_add_page_form_submit($form, &$form_state) 
{
    if (isset($form['step1']['add_submit']['#value']) &&
        $form_state['triggering_element']['#value'] == $form['step1']['add_submit']['#value'])
    {
        $stage = $_GET['stage'] or die("Неверный запрос");
        $curriculum_num = $form_state['values']['step1']['curriculum_num'];

        if (isset($form['step1']['direction_block']['direction_code']))
            $direction_code = $form['step1']['direction_block']['direction_code']['#options']
                [$form_state['values']['step1']['direction_block']['direction_code']];
        else
            $direction_code = $form_state['values']['step1']['direction_block']['new_direction_code'];
        if (isset($form['step1']['direction_block']['direction_name']))
            $direction_name = $form_state['values']['step1']['direction_block']['direction_name'];
        else
            $direction_name = $form_state['values']['step1']['direction_block']['new_direction_name'];

        $server = 'localhost';
        $username = 'moevm_user';
        $password = 'Pwt258E6JT8QAz3y';
        $database = 'moevmdb';

        $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
        mysqli_query ($mysqli, "SET NAMES `utf8`");

        $is_direction_result = $mysqli->query("SELECT `idDirection`
                     FROM direction
                     WHERE `DirectionCode` = '" . $direction_code . "'");
        if($row = $is_direction_result->fetch_row())
        {
            $direction_id = $row[0];
            $is_direction_result->close();
        }
        else
        {
            $direction_result = $mysqli->query("INSERT INTO direction
                    (`DirectionCode`, `DirectionName`)
                    VALUES ('" . $direction_code . "', '" . $direction_name . "')");
            $id_direction_result = $mysqli->query("SELECT `idDirection`
                     FROM direction
                     WHERE `DirectionCode` = '" . $direction_code . "'"); 
            $row = $id_direction_result->fetch_row();
            $direction_id = $row[0];
            $id_direction_result->close();  
        }
        $mysqli->query("INSERT INTO curriculum
                    (`CurriculumNum`, `Direction`, `Chair`, `Stage`)
                    VALUES ('" . $curriculum_num . "', '" . $direction_id . "', 1, '" . $stage . "')");
        $mysqli->close();
        drupal_goto('disciplines/plan');
    }
    else if (isset($form['step1']['direction_block']['add_new_dir_submit']['#value']) &&
        $form_state['triggering_element']['#value'] == $form['step1']['direction_block']['add_new_dir_submit']['#value']) {
        $form_state['storage']['is_new_dir'] = 1;
    }
    else if (isset($form['step1']['direction_block']['back_new_dir_submit']['#value']) &&
        $form_state['triggering_element']['#value'] == $form['step1']['direction_block']['back_new_dir_submit']['#value']) {
        $form_state['storage']['is_new_dir'] = 0;
    }
    else {
        // Сохраняем состояние формы, полученное при переходе на новый шаг.
        if(isset($form_state['storage']['step']))
            $current_step = 'step' . $form_state['storage']['step'];
        else
        {
            $current_step = 'step1';
            $form_state['storage']['step'] = 1;
        }

        if (!empty($form_state['values'][$current_step]))
        {
            $form_state['storage']['values'][$current_step] = $form_state['values'][$current_step];
        }

        // Если перешли на следующий шаг - то увеличиваем счётчик шагов.
        if (isset($form['step1']['load_file_block']['load_submit']['#value']) && $form_state['triggering_element']['#value'] == $form['step1']['load_file_block']['load_submit']['#value'])
        {
            $form_state['storage']['step']++;

            $file = $form_state['values']['step1']['load_file_block']['load_file'];

            // подключаем библиотеку для работы с файлом
            require_once drupal_get_path('module', 'discipline') . "/Classes/PHPExcel.php";
            $path = 'public://documents/';
            $path = drupal_realpath($path);
            $excel = PHPExcel_IOFactory::load($path . "/" . $file->filename);
            $excel->setActiveSheetIndexByName("Шахм");
            $current_sheet = $excel->getActiveSheet();
            $stage_name = $current_sheet->getCellByColumnAndRow(0, 1)->getValue();

            if(mb_stripos($stage_name, "бакалавры") !== false)
            {
                $stage = 1;
            }
            if(mb_stripos($stage_name, "магистры") !== false)
            {
                $stage = 2;
            }

            //функция парсит файл и возвращает список дисциплин
            if($stage == 1)
            {
                $discipline_list = discipline_plan_view_page_parse_curriculum($excel);
            }
            else if ($stage == 2)
            {
                $discipline_list = discipline_plan_view_page_parse_master_curriculum($excel);
            }

            $curriculum_info = discipline_plan_view_page_parse_new($excel);

            //формируем таблицу из полученного результата
            $header = array('Индекс', 'Дисциплина', 'Сем', 'Контроль', 'Всего',
                'Лек', 'Лаб', 'Практ', 'СРС', 'ЗЕТ', 'Каф');
            $table = theme('table', array('header' => $header, 'rows' => $discipline_list));
            file_delete($file);

            $output = "<b>Номер учебного плана:</b>  " . $curriculum_info["curriculum_num"];
            $output .= "<br><b>Код направления:</b>  " . $curriculum_info["direction_code"];
            $output .= "<br><b>Название направления:</b>  " . $curriculum_info["direction_name"];

            $output .= "<br>" . $table;

            $form_state['storage']['table'] = $output;
            $form_state['storage']['discipline_list'] = $discipline_list;
            $form_state['storage']['curriculum_info'] = $curriculum_info;

            $step_name = 'step' . $form_state['storage']['step'];

            if (!empty($form_state['storage']['values'][$step_name]))
            {
                $form_state['values'][$step_name] = $form_state['storage']['values'][$step_name];
            }
        }

        // Если вернулись на шаг назад - уменьшаем счётчик шагов.
        if (isset($form['actions']['prev']['#value']) && $form_state['triggering_element']['#value'] == $form['actions']['prev']['#value'])
        {
            $form_state['storage']['step']--;

            // Забираем из хранилища данные по предыдущему шагу и возвращаем их в форму.
            $step_name = 'step' . $form_state['storage']['step'];
            $form_state['values'][$step_name] = $form_state['storage']['values'][$step_name];
        }

        // Если была нажата кнопка "Отмена", возвращаемся к УП.
        if (isset($form['actions']['cancel']['#value']) && $form_state['triggering_element']['#value'] == $form['actions']['cancel']['#value'])
        {
            drupal_goto("/disciplines/plan");
        }

        // Если пользователь прошёл все шаги и нажал на кнопку "Принять",
        // то обрабатываем полученные данные со всех шагов.
        if (isset($form['actions']['submit']['#value']) && $form_state['triggering_element']['#value'] == $form['actions']['submit']['#value'])
        {
            // заносим данные в базу данных
            $stage = $_GET['stage'];
            $curriculum_info = $form_state['storage']['curriculum_info'];

            $server = 'localhost';
            $username = 'moevm_user';
            $password = 'Pwt258E6JT8QAz3y';
            $database = 'moevmdb';

            $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
            mysqli_query ($mysqli, "SET NAMES `utf8`");

            $is_direction_result = $mysqli->query("SELECT `idDirection`
                     FROM direction
                     WHERE `DirectionCode` = '" . $curriculum_info["direction_code"] . "'");
            if($row = $is_direction_result->fetch_row())
            {
                $direction_id = $row[0];
                $is_direction_result->close();
            }
            else
            {
                $direction_result = $mysqli->query("INSERT INTO direction
                    (`DirectionCode`, `DirectionName`)
                    VALUES ('" . $curriculum_info["direction_code"] . "', '" . $curriculum_info["direction_name"] . "')");

                $id_direction_result = $mysqli->query("SELECT `idDirection`
                     FROM direction
                     WHERE `DirectionCode` = '" . $curriculum_info["direction_code"] . "'");

                $row = $id_direction_result->fetch_row();
                $direction_id = $row[0];
                $id_direction_result->close();
            }

            $mysqli->query("INSERT INTO curriculum
                    (`CurriculumNum`, `Direction`, `Chair`, `Stage`)
                    VALUES ('" . $curriculum_info["curriculum_num"] . "', '" . $direction_id . "', 1, '" . $stage . "')");

            $curriculum_id_result = $mysqli->query("SELECT `idCurriculum`
                     FROM curriculum
                     WHERE `CurriculumNum` = '" . $curriculum_info["curriculum_num"] . "'");
            if($row = $curriculum_id_result->fetch_row())
            {
                $curriculum_id = $row[0];
                $curriculum_id_result->close();
            }
            $mysqli->close();

            $table = $form_state['storage']['discipline_list'];
            discipline_plan_load_disciplines_save($curriculum_id, $table);

            $form_state['rebuild'] = FALSE;

            drupal_goto('disciplines/plan');
        }

    }
    // Указываем, что форма должна быть построена заново.
    $form_state['rebuild'] = TRUE;
}

function discipline_plan_view_page_parse_new($curriculum)
{
    require_once drupal_get_path('module', 'discipline') . "/Classes/PHPExcel.php";

    $curriculum->setActiveSheetIndexByName("Шахм");
    $current_course_sheet = $curriculum->getActiveSheet();
    foreach( $current_course_sheet->getRowIterator() as $row ) 
    {
        foreach( $row->getCellIterator() as $cell ) 
        {
            $value = $cell->getValue();
            $pos = mb_stripos($value, "УЧЕБНЫЙ");
            if($pos !== false)
            {
                $curriculum_num = $current_course_sheet->getCellByColumnAndRow(PHPExcel_Cell::columnIndexFromString($cell->getColumn()), $cell->getRow())->getValue();
            }

            $pos = mb_stripos($value, "Направление подготовки");
            if($pos !== false)
            {
                $direction_code = $current_course_sheet->getCellByColumnAndRow(PHPExcel_Cell::columnIndexFromString($cell->getColumn()), $cell->getRow())->getValue();
                if(!$direction_name = $current_course_sheet->getCellByColumnAndRow(PHPExcel_Cell::columnIndexFromString($cell->getColumn())+1, $cell->getRow())->getValue()) 
                    $direction_name = $current_course_sheet->getCellByColumnAndRow(PHPExcel_Cell::columnIndexFromString($cell->getColumn())+2, $cell->getRow())->getValue();
                break 2;
            }
        }
    }

    $split_num = preg_split("/[\s,-]+/", $curriculum_num);
    $result = array(
        "curriculum_num" => $split_num[0],
        "direction_code" => $direction_code,
        "direction_name" => str_replace('"',"",$direction_name)
        );

    return $result;
}

function discipline_plan_view_page_parse_master_curriculum($curriculum)
{
    $nums_of_columns = array();
    require_once drupal_get_path('module', 'discipline') . "/Classes/PHPExcel.php";

    $curriculum->setActiveSheetIndexByName("Курс5");

    $current_course_sheet = $curriculum->getActiveSheet();
    foreach( $current_course_sheet->getRowIterator() as $row ) 
    {
        if(count($nums_of_columns) == 16) break;
        foreach( $row->getCellIterator() as $cell ) 
        {
            if(count($nums_of_columns) == 16) break;
            $value = $cell->getValue();
            switch ($value) {
                case '№':
                    $nums_of_columns["Number"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'Индекс':
                    $nums_of_columns["Index"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'Наименование':
                    $nums_of_columns["Dis_name"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'Семестр 9':
                    $nums_of_columns["Sem1"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'Семестр A':
                    $nums_of_columns["Sem2"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'Лек':
                    if(PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1 < $nums_of_columns["Sem2"])
                      $nums_of_columns["Lec1"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    else
                      $nums_of_columns["Lec2"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'Лаб':
                    if(PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1 < $nums_of_columns["Sem2"])
                      $nums_of_columns["Lab1"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    else
                      $nums_of_columns["Lab2"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'Пр':
                    if(PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1 < $nums_of_columns["Sem2"])
                      $nums_of_columns["Pract1"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    else
                      $nums_of_columns["Pract2"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                 case 'СРС':
                    if(PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1 < $nums_of_columns["Sem2"])
                      $nums_of_columns["Solo1"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    else
                      $nums_of_columns["Solo2"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'ЗЕТ':
                    if(PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1 < $nums_of_columns["Sem2"])
                      $nums_of_columns["Zet1"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    else
                      $nums_of_columns["Zet2"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
                case 'Каф.':
                    $nums_of_columns["Chair"] = PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1;
                    break;
            }
        }
    }
    $discipline_list = array();

    $semesters = array(
      1 => "9",
      2 => "A",
      3 => "B",
      4 => "C");

    $last_row = 0;
    for($i = 5; $i < 7; $i++)
    {
        $curriculum->setActiveSheetIndexByName("Курс" . $i);
        $current_course_sheet = $curriculum->getActiveSheet();

        $highest_row = $current_course_sheet->getHighestRow();
        for ($row = $last_row + 1; $row <= $highest_row; $row++)
        {
            $cell = $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Number"], $row);
            $value = $cell->getValue();
            while(is_numeric($value))
            {
                $cell = $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Dis_name"], $row);
                $value = $cell->getValue();

                if($current_course_sheet->getCellByColumnAndRow($nums_of_columns["Lec1"], $row)->getValue() || 
                $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Lab1"], $row)->getValue() || 
                $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Pract1"], $row)->getValue())
                {
                    $discipline_list[] = array(
                    "Index" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Index"], $row)->getValue(),
                    "Dis_name" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Dis_name"], $row)->getValue(),
                    "Sem" => $semesters[$i * 2 - 9],
                    "Control" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Sem1"], $row)->getValue(),
                    "Total" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Sem1"] + 1, $row)->getValue(),
                    "Lec" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Lec1"], $row)->getValue(),
                    "Lab" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Lab1"], $row)->getValue(),
                    "Pract" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Pract1"], $row)->getValue(),
                    "Solo" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Solo1"], $row)->getValue(),
                    "Zet" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Zet1"], $row)->getValue(),
                    "Chair" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Chair"], $row)->getValue());
                }

                if($current_course_sheet->getCellByColumnAndRow($nums_of_columns["Lec2"], $row)->getValue() || 
                $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Lab2"], $row)->getValue() || 
                $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Pract2"], $row)->getValue())
                {
                    $discipline_list[] = array(
                    "Index" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Index"], $row)->getValue(),
                    "Dis_name" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Dis_name"], $row)->getValue(),
                    "Sem" => $semesters[$i * 2 - 8],
                    "Control" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Sem2"], $row)->getValue(),
                    "Total" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Sem2"] + 1, $row)->getValue(),
                    "Lec" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Lec2"], $row)->getValue(),
                    "Lab" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Lab2"], $row)->getValue(),
                    "Pract" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Pract2"], $row)->getValue(),
                    "Solo" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Solo2"], $row)->getValue(),
                    "Zet" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Zet2"], $row)->getValue(),
                    "Chair" => $current_course_sheet->getCellByColumnAndRow($nums_of_columns["Chair"], $row)->getValue());
                } 
                
              
                $last_row = $row;
            }
         }
    }
   return $discipline_list;  
}

function discipline_plan_add_dir_codes() {
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb';

    $direction_codes = array();

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $direction_result = $mysqli->query("SELECT `DirectionCode`, `DirectionName`
		FROM direction");

    $mysqli->close();

    foreach ($direction_result as $row)
    {
        $direction_codes[$row['DirectionCode']] = $row['DirectionName'];
       // array_push($direction_codes, $row['DirectionCode']);
    }

    $direction_result->close();

   // dsm($direction_codes);

    return $direction_codes;
}

function discipline_plan_add_dir_name() {

}

