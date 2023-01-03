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

// Extend the default palette
PaletteManipulator::create()
    ->addLegend('rszjahresprogrammp_legend', 'amg_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField(['rszjahresprogrammp'], 'rszjahresprogrammp_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_user');

// Add fields to tl_user_group
$GLOBALS['TL_DCA']['tl_user']['fields']['rszjahresprogrammp'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => [
        'create',
        'delete',
    ],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => ['multiple' => true],
    'sql'       => 'blob NULL',
];
