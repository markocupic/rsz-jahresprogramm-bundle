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

use Contao\Date;
use Contao\Widget;
use Markocupic\ExportTable\Config\Config;

/**
 * @Hook(ExportTableListener::HOOK, priority=ExportTableListener::PRIORITY)
 */
class ExportTableListener
{
    public const HOOK = 'exportTable';
    public const PRIORITY = 1;

    public function __invoke(string $strFieldname, $varValue, string $strTablename, array $arrDataRecord, array $arrDca, Cofig $objConfig): string
    {

        // tl_jahresprogramm
        if ($strTablename === 'tl_rsz_jahresprogramm') {

            // html entity decode  z.B. &#40; -> (
            $varValue = html_entity_decode((string)$varValue);

        }

        return $varValue;
    }
}
