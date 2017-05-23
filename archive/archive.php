<?php
/**
 * Created by PhpStorm.
 * User: annaf
 * Date: 21.05.2017
 * Time: 20:18
 */

function archive_main_form($form, &$form_state) {

    $years = get_available_years();
    $sections = array('Преподаватели', 'Дисциплины', 'Учебные планы', 'Группы');

    $form['year'] = array(
        '#type' => 'select',
        '#title' => t('Выберите год:'),
        '#options' => $years,
        '#default_value' => 0,
    );

    $form['section'] = array(
        '#type' => 'select',
        '#title' => t('Выберите раздел:'),
        '#options' => $sections,
        '#default_value' => 0,
    );

    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => 'Посмотреть',
    );

    return $form;
}

function archive_main_form_submit($form, &$form_state){
    $year = $form['year']['#options'][$form_state['values']['year']];
    $section = $form_state['values']['section'];

    switch($section) {
        case 0:
            drupal_goto('archive/teachers', array('query' => array('year' => $year)));
            break;
        case 1:
            drupal_goto('archive/disciplines', array('query' => array('year' => $year)));
            break;
        case 2:
            drupal_goto('archive/curriculums', array('query' => array('year' => $year)));
            break;
        case 3:
            drupal_goto('archive/groups', array('query' => array('year' => $year)));
            break;
        default:
            break;
    }
}

function get_available_years() {
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb';

    $years = array();

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $db_result = mysqli_query($mysqli, "SHOW DATABASES ");

    $mysqli->close();

    if($db_result) {
        foreach ($db_result as $row) {
            if (strpos($row['Database'], 'moevmdb_archive_') !== false) {
                $years[] = substr($row['Database'], -4);
            }
        }
        $db_result->close();
    }

    return $years;
}
