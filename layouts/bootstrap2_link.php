<?php

use Joomla\CMS\Language\Text;
use \Joomla\CMS\Date\Date;

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
$cert_date_start = new Date($displayData['cert_date_start']);
$cert_date_end = new Date($displayData['cert_date_end']);

//echo '<pre>';
//print_r($displayData);
//echo '</pre>';

$tooltip = 'Документ подписан электронной подписью. <ul>';
$tooltip .= '<li><strong>Организация</strong> '. htmlspecialchars($displayData['organisation']).'</li>';
$tooltip .= '<li><strong>Директор</strong> '. htmlspecialchars($displayData['common_name']).'</li>';
$tooltip .= '<li><strong>Дата создания</strong> '. $displayData['pdf_date_modified'].'</li>';
$tooltip .= '<li><strong>Сертификат</strong> '. $displayData['serial_number'].'</li>';
$tooltip .= '<li><strong>Период действия сертификата</strong> '. $cert_date_start->format(Text::_('DATE_FORMAT_FILTER_DATE')).'-'.$cert_date_end->format(Text::_('DATE_FORMAT_FILTER_DATE')).'</li>';
$tooltip .= '</ul>';
$link = '<img src="'.$displayData['sign_icon'].'" alt="Документ подписан цифровой подписью"/> <a href="'.$displayData['link_to_file'].'" class="hasTooltip" data-toggle="tooltip" data-html="true" title="'.$tooltip.'">Скачать файл</a>';
echo $link;
