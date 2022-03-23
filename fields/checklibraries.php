<?php
/**
 * @package       WebTolk plugin info field
 * @version       1.0.0
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2020 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
 * @since         1.0.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Helper\LibraryHelper;
use Joomla\CMS\Language\Text;
use \Joomla\CMS\Factory;

FormHelper::loadFieldClass('note');

class JFormFieldChecklibraries extends JFormFieldNote
{

	protected $type = 'checklibraries';

	/**
	 * Method to get the field input markup for a spacer.
	 * The spacer does not have accept input.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.7.0
	 */
	protected function getInput()
	{
		return '';

	}

	/**
	 * @return  string  The field label markup.
	 *
	 * @since   1.7.0
	 */
	protected function getLabel()
	{

		$libraries_path = [
			'administrator/manifests/libraries/Sop/ASN1.xml',
			'administrator/manifests/libraries/Sop/CryptoEncoding.xml',
			'administrator/manifests/libraries/Webmasterskaya/CryptoBridge.xml',
			'administrator/manifests/libraries/Webmasterskaya/CryptoTypes.xml',
			'administrator/manifests/libraries/Webmasterskaya/X501.xml',
			'administrator/manifests/libraries/Webmasterskaya/X509.xml',
			'administrator/manifests/libraries/Smalot/PdfParser.xml',
		];
		$table_rows     = '';
		foreach ($libraries_path as $library)
		{
			$table_rows .= '<tr>';

			if (File::exists(JPATH_SITE . '/' . $library))
			{
				$library_xml      = simplexml_load_file(JPATH_SITE . '/' . $library);
				$table_rows       .= '<td><h5>' . $library_xml->name . '</h5><p><small>' . $library_xml->description . '</small> <a href="' . $library_xml->authorUrl . '" target="_blank">' . $library_xml->authorUrl . '</a></p></td>';
				$table_rows       .= '<td><span class="badge badge-success bg-success">v.' . $library_xml->version . '</span></td>';
				$status_css_class = ((LibraryHelper::isEnabled((string) $library_xml->libraryname)) ? 'badge-success bg-success' : 'badge-important bg-danger');
				$table_rows       .= '<td><span class="badge ' . $status_css_class . '">' . ((LibraryHelper::isEnabled((string) $library_xml->libraryname)) ? Text::_('JENABLED') : Text::_('JDISABLED')) . '</span></td>';
			}
			else
			{
				$file = File::stripExt(basename($library));

				if($file == 'ASN1'){
					$download_link = 'https://web-tolk.ru/get.html?element=sop-asn1-library';
				} elseif($file == 'CryptoEncoding'){
					$download_link = 'https://web-tolk.ru/get.html?element=sop-crypto-encoding';
				} elseif($file == 'CryptoBridge'){
					$download_link = 'https://web-tolk.ru/get.html?element=webmasterskaya_crypto_bridge';
				} elseif($file == 'CryptoTypes'){
					$download_link = 'https://web-tolk.ru/get.html?element=webmasterskaya_crypto_types';
				} elseif($file == 'X501'){
					$download_link = 'https://web-tolk.ru/get.html?element=webmasterskaya_x501';
				} elseif($file == 'X509'){
					$download_link = 'https://web-tolk.ru/get.html?element=webmasterskaya_X509';
				} elseif($file == 'PdfParser'){
					$download_link = 'https://web-tolk.ru/get.html?element=smalotpdfparser';
				}


				$table_rows .= '<td colspan="3"><h5 style="color:red;">Library ' . $file . ' is not found!</h5><p>You can <a href="'.$download_link.'">download</a> it and install manually.</p></td>';
			}
			$table_rows .= '</tr>';
		}
//		return ;
		$html = '
		<div class="wt-b24-plugin-info">
			<div style="padding: 0px 15px;" class="span12 col-12">
				<table class="table table-bordered table-striped table-hover">
				<caption><h4>'.Text::_('PLG_WT_DIGITALLY_SIGNED_PDF_LIBRARY_CHECK').'</h4></caption>
				' . $table_rows . '
				
                </table>
			</div>
		</div>
        ';

		echo $html;
		return '';
	}

	/**
	 * Method to get the field title.
	 *
	 * @return  string  The field title.
	 *
	 * @since   1.7.0
	 */
	protected function getTitle()
	{
		return $this->getLabel();
	}

}

?>