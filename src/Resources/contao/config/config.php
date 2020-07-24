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
 * Backend modules
 */
$GLOBALS['BE_MOD']['rsz_tools']['rsz_jahresprogramm'] = array(
    'tables' => ['tl_rsz_jahresprogramm']
);

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_rsz_jahresprogramm'] = Markocupic\RszJahresprogrammBundle\Model\RszJahresprogrammModel::class;
$GLOBALS['TL_MODELS']['tl_rsz_jahresprogramm_participant'] = Markocupic\RszJahresprogrammBundle\Model\RszJahresprogrammParticipantModel::class;

/**
 * CSS
 */
if(TL_MODE === 'BE')
{
    $GLOBALS['TL_CSS'][] = 'bundles/markocupicrszjahresprogramm/style_be.css';
}
