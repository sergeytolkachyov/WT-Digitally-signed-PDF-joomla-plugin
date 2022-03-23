<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @var $displayData array Digital sign data
 * Use
 *      echo '<pre>';
 *		print_r($displayData);
 *		echo '</pre>';
 *
 * Информация в массиве с данными подписи может быть очень разная в зависимости от типа подписи, производителя.
 * Поэтому смотрим массив $displayData и отображаем только нужную информацию.
 * Array
 *   (
 *       [pdf_date_modified] => дата последнего изменения pdf-файла. Как правило, это дата подписания.
 *       [inn] => ИНН
 *       [snils] => СНИЛС
 *       [email] => электронная почта
 *       [country] => RU - двухсимвольный код страны
 *       [province] => регион/область
 *       [city] => город
 *       [organisation] => название организации
 *       [given_name] => имя и отчество должностного лица
 *       [surname] => фамилия должностного лица
 *       [common_name] => Ф.И.О. целиком
 *       [post] => должность
 *       [cert_date_start] => дата начала действия сертификата электронной подписи
 *       [cert_date_end] => дата окончания действия сертификата электронной подписи
 *       [serial_number] => серийный номер
 *       [link_to_file] => ссылка на файл
 *       [sign_icon] => иконка ЭЦП из настроек плагина
 *   )
 *
 */
// echo '<pre>';
// print_r($displayData);
// echo '</pre>';


?>

<?php
$link_to_file = $displayData['link_to_file'];
$sign_icon = $displayData['sign_icon'];
$details = '<details class="well"><summary><img src="'.$sign_icon.'" alt="Документ подписан цифровой подписью"/>  Скачать файл</summary> 
            <p>Документ подписан электронной подписью. <ul>';
	        $details .= '<li><strong>Организация</strong> '. htmlspecialchars($displayData['organisation']).'</li>';
	        $details .= '<li><strong>Дата создания</strong> '. htmlspecialchars($displayData['pdf_date_modified']).'</li>';
	        $details .= '<li><strong>Сертификат</strong> '. htmlspecialchars($displayData['serial_number']).'</li>';
	        $details .= '<li><strong>Период действия сертификата</strong> '. $displayData['cert_date_start'].'-'.$displayData['cert_date_start'].'</li>';
$details .= '</ul>
            </p>
            <a href="'.$link_to_file.'" class="btn btn-primary">Скачать файл</a>
            </details>';

echo $details;
