<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    RSZ Benutzerverwaltung
 * @license    MIT
 * @see        https://github.com/markocupic/rsz-benutzerverwaltung-bundle
 *
 */

namespace Markocupic\RszBenutzerverwaltungBundle\EventListener\ContaoHooks;

use Contao\Widget;
use Contao\CoreBundle\ServiceAnnotation\Hook;

/**
 * @Hook(AddCustomRegexpListener::HOOK, priority=AddCustomRegexpListener::PRIORITY)
 *
 * Class AddCustomRegexpListener
 * @package Markocupic\RszBenutzerverwaltungBundle\EventListener\ContaoHooks
 */
class AddCustomRegexpListener
{
    const HOOK = 'addCustomRegexp';
    const PRIORITY = 10;

    /**
     * Überprüfe, ob Name und Vorname übergeben wurden (mind. 2 Wörter)
     */
    public function __invoke(string $strRegexp, string $varValue, Widget $objWidget): bool
    {
        // Überprüfe, ob Name und Vorname übergeben wurden (mind. 2 Wörter)
        if ($strRegexp === 'name')
        {
            if (strpos(trim($varValue), ' ') === false)
            {
                $objWidget->addError('Der Name sollte aus mindestens zwei durch einen Leerschlag voneinander getrennten Wörtern bestehen.');
            }

            return true;
        }

        return false;
    }
}
