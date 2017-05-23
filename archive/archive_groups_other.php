<?php
/**
 * Created by PhpStorm.
 * User: annaf
 * Date: 23.05.2017
 * Time: 4:41
 */

function archive_groups_other_page() {
    $year = $_GET['year'];

    $output = "<h2>Данные за " . $year . " год</h2><br>";

    $groups = archive_groups_other_split_all($year);
    $tables = get_archive_other_groups_tables($groups, $year);

    $header = array('', 'Группа', 'Численность', 'Год создания', 'E-mail', 'Учебный план');

    for($i = 1; $i <= 7; $i++)
    {
        if(!empty($tables[$i]['rows']))
        {

            $output .= "<h2>" . $tables[$i]['fac'] . "</h2>";

             $output .= theme('table', array('header' => $header, 'rows' => $tables[$i]['rows']));
        }
    }

    return $output;
}

function archive_groups_other_split_all($year)
{
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive_' . $year;

    $groups = array(
        1 => NULL,
        2 => NULL,
        3 => NULL,
        4 => NULL,
        5 => NULL,
        6 => NULL,
        7 => NULL,
    );

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");

    $groups_result = mysqli_query ($mysqli, "SELECT * FROM `group`");

    foreach ($groups_result as $row)
    {
        $curriculum_result =  mysqli_query ($mysqli, "SELECT `CurriculumNum`, `Chair` FROM `curriculum`
		WHERE `idCurriculum` = '" . $row['Curriculum'] . "'");

        $curriculum = $curriculum_result->fetch_assoc();
        $curriculum_result->close();

        $chair_result =  mysqli_query ($mysqli, "SELECT `ChairNum` FROM `chair`
		WHERE `idChair` = '" . $curriculum['Chair'] . "'");

        $chair = $chair_result->fetch_assoc();
        $chair_result->close();
        $row['CurriculumNum'] = $curriculum['CurriculumNum'];

        if($chair['ChairNum'] != 14 && !empty($chair))
        {
            switch (mb_substr($row['GroupNum'], 1, 1)) {
                case '1':
                    $groups['1'][] = $row;
                    break;
                case '2':
                    $groups['2'][] = $row;
                    break;
                case '3':
                    $groups['3'][] = $row;
                    break;
                case '4':
                    $groups['4'][] = $row;
                    break;
                case '5':
                    $groups['5'][] = $row;
                    break;
                case '6':
                    $groups['6'][] = $row;
                    break;
                case '7':
                    $groups['7'][] = $row;
                    break;
            }
        }
    }
    $groups_result->close();
    $mysqli->close();

    return $groups;
}

function get_archive_other_groups_tables($groups, $year)
{
    $server = 'localhost';
    $username = 'moevm_user';
    $password = 'Pwt258E6JT8QAz3y';
    $database = 'moevmdb_archive_' . $year;

    $mysqli = new \MySQLi($server, $username, $password, $database) or die(mysqli_error());
    mysqli_query ($mysqli, "SET NAMES `utf8`");
    $tables = array();

    for($i = 1; $i <= 7; $i++)
    {
        $rows = array();
        $faculty_result = mysqli_query ($mysqli, "SELECT `FacultyShortName` FROM `faculty`
			WHERE `FacultyNum` = '" . $i . "'");

        $faculty = $faculty_result->fetch_assoc();
        $faculty_result->close();

        $tables[$i]['fac'] = $faculty['FacultyShortName'];


        for($j = 0; $j < count($groups[$i]); $j++)
        {
            $rows[] = array(
                "<a href='other/view?id=" . $groups[$i][$j]['idGroup'] . "&year=" . $year .
                "'  title='просмотр'><img src='/sites/all/pic/edit.png'></a>",
                $groups[$i][$j]['GroupNum'],
                $groups[$i][$j]['Size'],
                $groups[$i][$j]['CreationYear'],
                $groups[$i][$j]['E-mail'],
                $groups[$i][$j]['CurriculumNum'],
            );
        }

        $tables[$i]['rows'] = $rows;
    }
    return $tables;
}