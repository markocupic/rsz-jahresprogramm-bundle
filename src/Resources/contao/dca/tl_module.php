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
 * Frontend modules
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['rsz_jahresprogramm_listing_module'] = '{title_legend},name,headline,type;{config_legend},rszJahresprogrammReaderPage;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['rsz_jahresprogramm_reader_module'] = '{title_legend},name,headline,type;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['rszJahresprogrammReaderPage'] = array
(
    'exclude'    => true,
    'inputType'  => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval'       => array('fieldType' => 'radio'),
    // do not set mandatory (see #5453)
    'sql'        => "int(10) unsigned NOT NULL default 0",
    'relation'   => array(
        'type' => 'hasOne',
        'load' => 'lazy',
    ),
);

/**
 * Class tl_module_rsz_jahresprogramm
 */
class tl_module_rsz_jahresprogramm extends Contao\Backend
{
    /**
     * Import the back end user object
     */
    public function __construct() {
        parent::__construct();
        $this->import('Contao\BackendUser', 'User');
    }

}
