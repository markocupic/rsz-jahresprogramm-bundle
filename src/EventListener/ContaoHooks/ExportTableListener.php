<?php

declare(strict_types=1);

/*
 * This file is part of Contao RSZ Jahresprogramm Bundle.
 *
 * (c) Marko Cupic 2023 <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/rsz-jahresprogramm-bundle
 */

namespace Markocupic\RszJahresprogrammBundle\EventListener\ContaoHooks;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Markocupic\ExportTable\Config\Config;

#[AsHook(ExportTableListener::HOOK, priority: 1)]
class ExportTableListener
{
    public const HOOK = 'exportTable';

    public function __invoke(string $strFieldName, $varValue, string $strTableName, array $arrDataRecord, array $arrDca, Config $objConfig): mixed
    {
        if ('tl_rsz_jahresprogramm' === $strTableName) {
            // html entity decode  z.B. &#40; -> (
            $varValue = html_entity_decode((string) $varValue);
        }

        return $varValue;
    }
}
