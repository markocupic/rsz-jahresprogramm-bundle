<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    RSZ Jahresprogramm
 * @license    MIT
 * @see        https://github.com/markocupic/rsz-jahresprogramm-bundle
 *
 */

/**
 * Add legend & fields
 */
\Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('tl_rsz_jahresprogramm_legend', 'default', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->addField(['tl_rsz_jahresprogramm_art', 'tl_jahresprogramm_wettkampfform', 'tl_jahresprogramm_trainingsstunden'], 'tl_rsz_jahresprogramm_legend', \Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_settings');

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['tl_rsz_jahresprogramm_art'] = [
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'long clr']
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['tl_jahresprogramm_wettkampfform'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['tl_jahresprogramm_wettkampfform'],
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'long clr']
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['tl_jahresprogramm_trainingsstunden'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['tl_jahresprogramm_trainingsstunden'],
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'long clr']
];

