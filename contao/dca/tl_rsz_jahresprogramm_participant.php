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

use Contao\DC_Table;
use Contao\DataContainer;
use Contao\Backend;

$GLOBALS['TL_DCA']['tl_rsz_jahresprogramm_participant'] = [
    // Config
    'config'      => [
        'dataContainer'     => DC_Table::class,
        'ptable'            => 'tl_member',
        'enableVersioning'  => true,
        'onsubmit_callback' => [
            [
                'tl_rsz_jahresprogramm_participant',
                'storeDateAdded',
            ],
        ],
        'sql'               => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index',
            ],
        ],
    ],
    // List
    'list'        => [
        'sorting'           => [
            'mode'        => DataContainer::MODE_SORTABLE,
            'fields'      => ['pid'],
            'flag'        => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label'             => [
            'fields'         => [
                'pid',
                'uniquePid',
            ],
            'showColumns'    => true,
            'label_callback' => [
                'tl_rsz_jahresprogramm_participant',
                'addIcon',
            ],
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm_participant']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm_participant']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm_participant']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg',
            ],
        ],
    ],
    // Palettes
    'palettes'    => [
        'default' => '{config_legend},addedOn;{personal_legend},member_id,signedOff,signedIn,signOffReason;',
    ],
    // Subpalettes
    'subpalettes' => [
    ],
    // Fields
    'fields'      => [
        'id'            => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid'           => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'uniquePid'     => [
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'tstamp'        => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'addedOn'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm_participant']['addedOn'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'rgxp'       => 'datim',
                'datepicker' => true,
                'tl_class'   => 'w50 wizard',
            ],
            'sql'       => "varchar(10) NOT NULL default ''",
        ],
        'signedOff'     => [
            'sql' => "char(1) NOT NULL default ''",
        ],
        'signedIn'      => [
            'sql' => "char(1) NOT NULL default ''",
        ],
        'signOffReason' => [
            'label'       => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm_participant']['signOffReason'],
            'exclude'     => true,
            'search'      => true,
            'inputType'   => 'textarea',
            'eval'        => ['rte' => 'tinyMCE'],
            'explanation' => 'insertTags',
            'sql'         => 'mediumtext NULL',
        ],
    ],
];

class tl_rsz_jahresprogramm_participant extends Backend
{

    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * Onsubmit callback
     * Store the date.
     */
    public function storeDateAdded(DataContainer $dc): void
    {
        // Front end call
        if (!($dc instanceof DataContainer)) {
            return;
        }

        // Return if there is no active record (override all)
        if (!$dc->activeRecord || $dc->activeRecord->addedOn > 0) {
            return;
        }

        $time = time();

        $this->Database->prepare('UPDATE tl_rsz_jahresprogramm_participant SET addedOn=? WHERE id=?')->execute($time, $dc->id);
    }
}
