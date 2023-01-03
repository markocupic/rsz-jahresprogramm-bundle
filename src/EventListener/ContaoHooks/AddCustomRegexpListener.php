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

namespace Markocupic\RszBenutzerverwaltungBundle\EventListener\ContaoHooks;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Widget;

#[AsHook(AddCustomRegexpListener::HOOK, priority: AddCustomRegexpListener::PRIORITY)]
class AddCustomRegexpListener
{
    public const HOOK = 'addCustomRegexp';
    public const PRIORITY = 10;

    /**
     * Überprüfe, ob Name und Vorname übergeben wurden (mind. 2 Wörter).
     */
    public function __invoke(string $strRegexp, string $varValue, Widget $objWidget): bool
    {
        // Überprüfe, ob Name und Vorname übergeben wurden (mind. 2 Wörter)
        if ('name' === $strRegexp) {

            if (false === strpos(trim($varValue), ' ')) {
                $objWidget->addError('Der Name sollte aus mindestens zwei durch einen Leerschlag voneinander getrennten Wörtern bestehen.');
            }

            return true;
        }

        return false;
    }
}
