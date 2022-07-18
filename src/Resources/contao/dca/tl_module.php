<?php

declare(strict_types=1);

/*
 * This file is part of RSZ Jahresprogramm Bundle.
 *
 * (c) Marko Cupic 2022 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/rsz-jahresprogramm-bundle
 */

use Contao\Backend;

/*
 * Frontend modules
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['rsz_jahresprogramm_listing_module'] = '{title_legend},name,headline,type;{config_legend},rszJahresprogrammReaderPage;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['rsz_jahresprogramm_reader_module'] = '{title_legend},name,headline,type;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

/*
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['rszJahresprogrammReaderPage'] = [
    'exclude'    => true,
    'inputType'  => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval'       => ['fieldType' => 'radio'],
    // do not set mandatory (see #5453)
    'sql'        => 'int(10) unsigned NOT NULL default 0',
    'relation'   => [
        'type' => 'hasOne',
        'load' => 'lazy',
    ],
];

/**
 * Class tl_module_rsz_jahresprogramm.
 */
class tl_module_rsz_jahresprogramm extends Backend
{
    /**
     * Import the back end user object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('Contao\BackendUser', 'User');
    }
}
