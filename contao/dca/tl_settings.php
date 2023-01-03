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

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addLegend('tl_rsz_jahresprogramm_legend', 'default', PaletteManipulator::POSITION_APPEND)
    ->addField([
        'tl_rsz_jahresprogramm_art',
        'tl_jahresprogramm_wettkampfform',
        'tl_jahresprogramm_trainingsstunden',
    ], 'tl_rsz_jahresprogramm_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_settings');

/*
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['tl_rsz_jahresprogramm_art'] = [
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'long clr'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['tl_jahresprogramm_wettkampfform'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['tl_jahresprogramm_wettkampfform'],
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'long clr'],
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['tl_jahresprogramm_trainingsstunden'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['tl_jahresprogramm_trainingsstunden'],
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'long clr'],
];
