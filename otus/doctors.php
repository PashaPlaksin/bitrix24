<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION->SetTitle('Врачи');
$APPLICATION->SetAdditionalCSS('/otus/doctors.css');

use Otus\Doctors\DoctorsPropertyValuesTable as DoctorsProperty;
use Otus\Doctors\DoctorsProceduresPropertyValuesTable as DoctorsProcedure;


$text_html = '';
$text_footer = '<br><br><div><form action="doctors.php" method="post">
                    <table>
                        <tr>
                            <td><input type="submit" class="ad-submit-button" name="add_doctor" value="Добавить специалиста">
                            <td><input type="submit" class="ad-submit-button" name="add_procedure" value="Добавить процедуру">
                        </tr>
                    </table>
                </div>';

/**
 * @param int $id ID элемента из инфоблока Врачи
 * @return array  Информация по доктору
 */
function getDoctorById(int $id): array
{
    return DoctorsProperty::query()->setSelect([
        'LAST_NAME' => 'ELEMENT.NAME',
        'NAME' => 'IMYA',
        'SECOND_NAME' => 'OTCHESTVO',
        'PROCS' => 'OKAZYVAEMYE_PROTSEDURY',
        'ID' => 'ELEMENT.ID',
    ])->where('ID', $id)->fetch();
}

/**
 * @return array  Информация по всем докторам
 */
function getDoctorAll(): array
{
    return DoctorsProperty::query()->setSelect([
        'LAST_NAME' => 'ELEMENT.NAME',
        'NAME' => 'IMYA',
        'SECOND_NAME' => 'OTCHESTVO',
        'PROCS' => 'OKAZYVAEMYE_PROTSEDURY',
        'ID' => 'ELEMENT.ID',
    ])->fetchAll();
}

/**
 * @param array $doctor Информация по доктору
 * @return string Список процедур вида "процедура1,.... ,процедураN"
 */
function getDoctorProcedure(array $doctor): string
{
    $procedure = DoctorsProcedure::query()
        ->setSelect([
            'NAME' => 'ELEMENT.NAME'
        ])
        ->where('ELEMENT.ID', 'in', $doctor['PROCS'])
        ->fetchAll();
    return implode(', ', array_map(function ($i) {
        return $i['NAME'];
    }, $procedure));
}

/**
 * @return array Список процедур в виде массива
 */
function getDoctorAllProcedure(): array
{
    return DoctorsProcedure::query()
        ->setSelect([
            'NAME' => 'ELEMENT.NAME',
            'ID' => 'ELEMENT.ID'
        ])
        ->fetchAll();
}

/*
 * main page
 */
if (!empty($add_doctor_action)) {
        $data = [
            'NAME' => $_POST['LAST_NAME'],
            'IMYA' => $_POST['NAME'],
            'OTCHESTVO' => $_POST['SECOND_NAME'],
            'OKAZYVAEMYE_PROTSEDURY' => $_POST['PROCEDURES'],
        ];
    DoctorsProperty::add($data);
}
if (!empty($add_procedure_action)) {
    $data = [
        'NAME' => $_POST['NAME'],
    ];
    DoctorsProcedure::add($data);
}
if (!empty($update_doctors)){
    //Array ( [DOC_ID] => 35 [LAST_NAME] => Плаксин [NAME] => Павел [SECOND_NAME] => Юрьевич [PROCEDURES] => Array ( [0] => 36 ) [update_doctors] => Обновить )
    $data = [
        'IBLOCK_ELEMENT_ID' => $_POST['DOC_ID'],
        'NAME' => $_POST['LAST_NAME'],
        'IMYA' => $_POST['NAME'],
        'OTCHESTVO' => $_POST['SECOND_NAME'],
    ];
    CIBlockElement::SetPropertyValues($_POST['DOC_ID'],DoctorsProperty::IBLOCK_ID,$_POST['PROCEDURES'],"OKAZYVAEMYE_PROTSEDURY");
    DoctorsProperty::update($_POST['DOC_ID'],$data);

}
if (!empty($del_doctors)){
    //Array ( [DOC_ID] => 35 [LAST_NAME] => Плаксин [NAME] => Павел [SECOND_NAME] => Юрьевич [PROCEDURES] => Array ( [0] => 36 ) [update_doctors] => Обновить )
    $data = [
        'IBLOCK_ELEMENT_ID' => $_POST['DOC_ID'],
    ];
    DoctorsProperty::delete($data);

}
if (empty($doctor) && empty($add_doctor) && empty($add_procedure) && empty($edit_doctors)) {
    $doctorAll = getDoctorAll();
    if (!empty($doctorAll)) {
        $i = 0;
        $text_html .= '<p><a>Специалисты больницы</a></p><div><form action="doctors.php" method="post"><table><tr>';
        foreach ($doctorAll as $key => $value) {
            $text_html .= '<td><input  type="submit" class="submit-button" name="doctor[' . $value['ID'] . ']"
                           value="' . $value['LAST_NAME'] . ' ' . $value['NAME'] . ' ' . $value['SECOND_NAME'] . '"></td>';
            $i++;
            if ($i == 3) {
                $text_html .= '</tr>';
            }
        }
        if ($i < 3) {
            $text_html .= '</tr>';
        }
    } else {
        die('<p>В списке "Врачи" не заведен ни один врач!</p>');
    }
    $text_html .= '</table></form></div>';
}
if (!empty($doctor)) {
    $doctorInfo = getDoctorById(key($doctor));
    if (!empty($doctorInfo)) {
        $procedureName = getDoctorProcedure($doctorInfo);
        $text_html .= '<p><a href="doctors.php">< Все специалисты больницы</a></p><form action="doctors.php" method="post">
                       <div>
                           <table border="1" bgcolor="#98FB98" cellspacing="0" cellpadding="5">
                               <tr bgcolor="#00FF7F">
                                   <td><b>Врач</b></td><td><b>Проводимые процедуры специалистом</b></td>
                               </tr>
                               <tr>
                                   <td>' . $doctor[key($doctor)] . '</td><td>' . $procedureName . '</td>
                               </tr>
                               <tr>
                                   <td><input type="hidden" name="DOC_ID" value="' . key($doctor) . '"></td>
                                   <td><input type="submit" class="ad-doctor-submit-button" name="edit_doctors" value="Редактировать"><br><br>
                                   <input type="submit" class="ad-doctor-submit-button" name="del_doctors" value="Удалить"></td>
                               </tr>
                           </table>
                       </div>';
    }

}
if (!empty($add_doctor)) {
    $allProcedures = getDoctorAllProcedure();
    $text_html .= '<p><a href="doctors.php">< Все специалисты больницы</a></p>
                       <div>
                          <p>Добавление специалиста:</p><form action="doctors.php" method="post">
                           <table>
                               <tr><td><b>Фамилия</b></td><td><input type="text" required name="LAST_NAME" id="LAST_NAME"></td></tr>
                               <tr><td><b>Имя</b></td><td><input type="text" required name="NAME" id="NAME"></td></tr>
                               <tr><td><b>Отчество</b></td><td><input type="text" required name="SECOND_NAME" id="SECOND_NAME"></td></tr>
                               <tr><td><b>Оказываемые процедуры</b></td><td><select required multiple size="5" id="PROCEDURES" name="PROCEDURES[]">';
    foreach ($allProcedures as $key => $value) {
        $text_html .= '<option value="' . $value['ID'] . '">' . $value['NAME'] . '</option>';
    }
    $text_html .= '            </td></tr>
                               <tr><td></td><td><input type="submit" class="ad-doctor-submit-button" name="add_doctor_action" value="Добавит специалиста"></td></tr>
                           </table>
                       </div>';
    $text_footer = '';
}
if (!empty($add_procedure)) {
    $text_html .= '<p><a href="doctors.php">< Все специалисты больницы</a></p>
                       <div>
                          <p>Добавление процедуры:</p><form action="doctors.php" method="post">
                           <table>
                               <tr><td><b>Название процедуры</b></td><td><input type="text" required name="NAME" id="NAME"></td></tr>
                               <tr><td></td><td><input type="submit" class="ad-doctor-submit-button" name="add_procedure_action" value="Добавит процедуру"></td></tr>
                           </table>
                       </div>';
    $text_footer = '';
}
if (!empty($edit_doctors)) {
    $doctorInfo = getDoctorById($_POST['DOC_ID']);
    if (!empty($doctorInfo)) {
        //Array ( [LAST_NAME] => Плаксин [NAME] => Пав [SECOND_NAME] => Юрьевич [PROCS] => Array ( [0] => 30 [1] => 31 ) [ID] => 35 )
        $allProcedures = getDoctorAllProcedure();
        $text_html .= '<p><a href="doctors.php">< Все специалисты больницы</a></p>
                       <div>
                           <p>Редактирование специалиста:</p><form action="doctors.php" method="post">
                           <table>
                               <tr><td>Фамилия</td><td><input type="hidden" name="DOC_ID" value="' . $_POST['DOC_ID'] . '">
                                                       <input type="text" required name="LAST_NAME" value="' . $doctorInfo['LAST_NAME'] . '"></td></tr>
                               <tr><td>Имя</td><td><input type="text" required name="NAME" value="' . $doctorInfo['NAME'] . '"></td></tr>
                               <tr><td>Отчество</td><td><input type="text" required name="SECOND_NAME" value="' . $doctorInfo['SECOND_NAME'] . '"></td></tr>
                               <tr><td>Проводимые процедуры</td><td><select required multiple size="5" id="PROCEDURES" name="PROCEDURES[]">';
        foreach ($allProcedures as $key => $value) {
            if (in_array($value['ID'], $doctorInfo['PROCS'])) {
                $text_html .= '<option selected value="' . $value['ID'] . '">' . $value['NAME'] . '</option>';
            } else {
                $text_html .= '<option value="' . $value['ID'] . '">' . $value['NAME'] . '</option>';
            }
        }
        $text_html .=            '</td></tr>
                               <tr>
                                   <td></td>
                                   <td><input type="submit" class="ad-doctor-submit-button" name="update_doctors" value="Обновить"></td>
                               </tr>
                           </table>
                       </div>';
    }
}
print ($text_html . $text_footer);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
