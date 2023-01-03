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

use Markocupic\RszJahresprogrammBundle\Controller\FrontendModule\RszJahresprogrammListingModuleController;
use Markocupic\RszJahresprogrammBundle\Controller\FrontendModule\RszJahresprogrammReaderModuleController;

/**
 * Backend modules
 */
$GLOBALS['TL_LANG']['MOD']['rsz_tools'] = 'RSZ Tools';
$GLOBALS['TL_LANG']['MOD']['rsz_jahresprogramm'] = ['RSZ Jahresprogramm', 'Jahresprogramm-Modul für Regionalkader Sportklettern Zentralschweiz'];

/**
 * Frontend modules
 */
$GLOBALS['TL_LANG']['FMD']['rsz_frontend_modules'] = 'RSZ Frontend Module';
$GLOBALS['TL_LANG']['FMD'][RszJahresprogrammListingModuleController::TYPE] = ['RSZ Jahresprogramm-Liste', 'Fügen Sie der Seite eine RSZ Jahresprogramm-Liste hinzu.'];
$GLOBALS['TL_LANG']['FMD'][RszJahresprogrammReaderModuleController::TYPE] = ['RSZ Jahresprogramm-Reader', 'Fügen Sie der Seite eineb RSZ Jahresprogramm-Reader hinzu.'];


