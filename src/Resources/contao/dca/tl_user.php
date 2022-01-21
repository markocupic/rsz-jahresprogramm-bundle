<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    RSZ Jahresprogramm
 * @license    MIT
 * @see        https://github.com/markocupic/rsz-jahresprogramm-bundle
 *
 */

// Extend the default palette
Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('rszjahresprogrammp_legend', 'amg_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addField(['rszjahresprogrammp'], 'rszjahresprogrammp_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_user');

// Add fields to tl_user_group
$GLOBALS['TL_DCA']['tl_user']['fields']['rszjahresprogrammp'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => [
        'create',
        'delete',
    ],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => ['multiple' => true],
    'sql' => "blob NULL",
];
