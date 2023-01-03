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

/*
 * Frontend modules
 */
$GLOBALS['TL_DCA']['tl_module']['palettes'][RszJahresprogrammListingModuleController::TYPE] = '{title_legend},name,headline,type;{config_legend},rszJahresprogrammReaderPage;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes'][RszJahresprogrammReaderModuleController::TYPE] = '{title_legend},name,headline,type;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

/*
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['rszJahresprogrammReaderPage'] = [
    'exclude'    => true,
    'inputType'  => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval'       => ['fieldType' => 'radio'],
    'sql'        => 'int(10) unsigned NOT NULL default 0',
    'relation'   => ['type' => 'hasOne', 'load' => 'lazy'],
];
