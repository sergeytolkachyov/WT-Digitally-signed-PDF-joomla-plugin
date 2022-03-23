<?php

/*
*   Copyright (C) 2021  Sergey Tolkachyov
*   Released under GNU GPL Public License
*   License: http://www.gnu.org/copyleft/gpl.html
*   https://web-tolk.ru
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use \Sop\ASN1\Element;
use \Sop\ASN1\Type\Constructed\Sequence;
use \Webmasterskaya\X509\Certificate\Certificate;
use \Smalot\PdfParser\Parser;

class plgSystemWt_digitally_signed_pdf extends CMSPlugin
{

	protected $autoloadlanguage = true;
	static $article = null;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array    $config   An optional associative array of configuration settings.
	 *
	 * @since   3.7.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

	}
	
	
	public function onAfterInitialise()
	{			
		$jversion = new JVersion();

		if (version_compare($jversion->getShortVersion(), '4.0', '<'))
		{
			// only for Joomla 3.x
			JLoader::registerNamespace('Sop', JPATH_LIBRARIES);
			JLoader::registerNamespace('Webmasterskaya', JPATH_LIBRARIES);
			JLoader::registerNamespace('Smalot', JPATH_LIBRARIES);

		}
		else
		{
			JLoader::registerNamespace('Sop', JPATH_LIBRARIES . '/Sop');
			JLoader::registerNamespace('Webmasterskaya', JPATH_LIBRARIES . '/Webmasterskaya');
			JLoader::registerNamespace('Smalot', JPATH_LIBRARIES. '/Smalot');
		}
	}

	public function onContentPrepare($context, $article, $params, $limitstart = 0)
	{
		//Проверка есть ли строка замены в контенте
		if (strpos($article->text, 'wt_ds_pdf') === false)
		{
			return;
		}

		// expression to search for
		$regex = "~{wt_ds_pdf}.*?{/wt_ds_pdf}~is";


		// process tags
		if (preg_match_all($regex, $article->text, $matches, PREG_PATTERN_ORDER))
		{

			// start the replace loop
			foreach ($matches[0] as $key => $match)
			{
				$pdf_file = preg_replace("/{.+?}/", "", $match);
				$pdf_file = str_replace(array('"', '\'', '`'), array('&quot;', '&apos;', '&#x60;'), $pdf_file); // Address potential XSS attacks

				$layoutId                          = $this->params->get('layout', 'default');
				$layout                            = new FileLayout($layoutId, JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'wt_digitally_signed_pdf' . DIRECTORY_SEPARATOR . 'layouts');

				$digital_sign_info                 = $this->getDigitallySignedPdfInfo($pdf_file);
				$digital_sign_info['link_to_file'] = $pdf_file;
				$digital_sign_info['sign_icon'] = 'media/plg_system_wt_digitally_signed_pdf/img/'. $this->params->get('sign_icon');
				$output                            = $layout->render($digital_sign_info);

				$article->text = str_replace($match, $output, $article->text);
			}

		}//end FOR

	} //onContentPrepare END

	public function getDigitallySignedPdfInfo(string $file_name)
	{
		$digital_sign_info = array();
		// Parse PDF file and build necessary objects.
		$parser        = new Parser();
		$pdf           = $parser->parseFile(JPATH_SITE . '/' . $file_name);
		$pdf_meta_data = $pdf->getDetails();
		//PDF file date modified
		$timezone            = Factory::getUser()->getTimezone();
		$ModDate             = new Date($pdf_meta_data['ModDate'], $timezone);
		$date_modified       = $ModDate->format(Text::_('DATE_FORMAT_LC6'),true);

		$digital_sign_info['pdf_date_modified'] = $date_modified;
		// Find and get the digital sign data
		// https://stackoverflow.com/questions/46430367/how-to-retrieve-digital-signature-information-from-pdf-with-php
		$content = file_get_contents(JPATH_SITE . '/' . $file_name);

		$regexp
			= '#ByteRange\[\s*(\d+) (\d+) (\d+)#'; // subexpressions are used to extract b and c

		$result = [];
		preg_match_all($regexp, $content, $result);

		if (isset($result[2]) && isset($result[3]) && isset($result[2][0])
			&& isset($result[3][0])
		)
		{
			$start = $result[2][0];
			$end   = $result[3][0];
			if ($stream = fopen(JPATH_SITE . '/' . $file_name, 'rb'))
			{
				$signature = stream_get_contents(
					$stream, $end - $start - 2, $start + 1
				); // because we need to exclude < and > from start and end

				fclose($stream);
			}

			if (!empty($signature))
			{
				$binary      = hex2bin($signature);
				$seq         = Sequence::fromDER($binary);
				$signed_data = $seq->getTagged(0)->asExplicit()->asSequence();
				$ecac        = $signed_data->getTagged(0)->asImplicit(Element::TYPE_SET)->asSet();
				/** @var Sop\ASN1\Type\UnspecifiedType $ecoc */
				$ecoc = $ecac->at($ecac->count() - 1);
				$cert = Certificate::fromASN1($ecoc->asSequence());

				foreach ($cert->tbsCertificate()->subject()->all() as $attr)
				{
					/** @var Webmasterskaya\X501\ASN1\AttributeTypeAndValue $atv */
					$atv      = $attr->getIterator()->current();
					$typeName = $atv->type()->typeName();
					$digital_sign_info[$this->getTagTranslation($typeName)] = $atv->value()->stringValue();
				}
				$digital_sign_info['cert_date_start'] = $cert->tbsCertificate()->validity()->notBefore()->dateTime()->format('d-m-Y H:i:s');
				$digital_sign_info['cert_date_end'] = $cert->tbsCertificate()->validity()->notAfter()->dateTime()->format('d-m-Y H:i:s');
				$digital_sign_info['serial_number'] = $this->dec2hex($cert->tbsCertificate()->serialNumber());
			}
		}

		return $digital_sign_info;
	}

	public function dec2hex($number)
	{
		$hexvalues = array('0', '1', '2', '3', '4', '5', '6', '7',
			'8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
		$hexval    = '';
		while ($number != '0')
		{
			$hexval = $hexvalues[bcmod($number, '16')] . $hexval;
			$number = bcdiv($number, '16', 0);
		}

		return $hexval;
	}


	public function getTagTranslation(string $key): string
	{
		$key = strtoupper($key);
		switch ($key):
			case 'UNSTRUCTUREDNAME':
			case 'UN':
				return 'unstructured_name';
			case 'C':
				return 'country'; // Двухсимвольный код страны согласно ГОСТ 7.67-2003 (ИСО 3166-1:1997)
			case 'S':
			case 'ST':
				return 'province';
			case 'STREET':
				return 'street';
			case 'O':
				return 'organisation';
			case 'T':
				return 'post';
			case 'ОГРН':
			case 'OGRN':
				return 'ogrn';
			case 'ОГРНИП':
			case 'OGRNIP':
				return 'ogrnip';
			case 'СНИЛС':
			case 'SNILS':
				return 'snils';
			case 'ИНН':
			case 'INN':
			case 'ИНН организации':
				return 'inn';
			case 'E':
			case 'EMAIL':
				return 'email';
			case 'L':
				return 'city';
			case 'CN':
				return 'common_name';// ФИО физ.лица, ИП или название юридического лица
			case 'SN':
				return 'surname';
			case 'G':
				return 'name';
			case 'OU':
				return 'department';
			case 'GIVENNAME':
				return 'given_name';
			default:
				return $key;
		endswitch;
	}

}