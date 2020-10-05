<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    RSZ Jahresprogramm
 * @license    MIT
 * @see        https://github.com/markocupic/rsz-jahresprogramm-bundle
 *
 */

namespace Markocupic\RszJahresprogrammBundle\EventListener\ContaoHooks;

use Contao\Config;
use Contao\Date;
use Contao\Widget;

/**
 * Class ExportTableListener
 * @package Markocupic\RszJahresprogrammBundle\EventListener\ContaoHooks
 */
class ExportTableListener
{

    /**
     * Updated: Marko Cupic, 26.07.2020
     * @param $fild
     * @param $value
     * @param $strTable
     * @param array $row
     * @return string
     */
    public function exportJahresprogramm($field, $value, $strTable, $row = null): string
    {

        // tl_jahresprogramm
        if ($strTable === 'tl_rsz_jahresprogramm') {
            //die(print_r($GLOBALS['TL_HOOKS']['exportTable'],true));
            // Parse to date
            if ($field === 'tstamp' || $field === 'start_date' || $field === 'end_date' || $field === 'registrationStop') {
                $value = Date::parse(Config::get('dateFormat'), $value);
            }

            // Convert arrays to comma seperated strings
            if (!empty($value) && is_array(unserialize($value))) {
                $value = implode(',', unserialize($value));
            }

            // html entity decode  z.B. &#40; -> (
            $value = html_entity_decode((string)$value);

        }

        return $value;
    }
}
