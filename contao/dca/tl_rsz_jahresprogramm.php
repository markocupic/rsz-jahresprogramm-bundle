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

use Contao\Config;
use Contao\System;
use Contao\DC_Table;
use Contao\DataContainer;

$GLOBALS['TL_DCA']['tl_rsz_jahresprogramm'] = [
    'config'      => [
        'dataContainer'    => DC_Table::class,
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list'        => [
        'sorting'           => [
            'mode'            => DataContainer::MODE_SORTED,
            'fields'          => ['start_date'],
            'flag'            => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout'     => 'filter;sort,search,limit',
            'disableGrouping' => true,
        ],
        'label'             => [
            'fields' => ['kw', 'art', 'teilnehmer'],
            'format' => '#STATUS# <span style="color:green;">KW %s, #datum#</span><span style="padding-left:10px; color:blue;">%s</span><span style="padding-left:10px;color:red;">[%s]</span> #signIn#',
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations'        => [
            'edit'        => [
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'copy'        => [
                'href' => 'act=copy',
                'icon' => 'copy.gif',
            ],
            'delete'      => [
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'show'        => [
                'label' => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
            'participant' => [
                'href'       => 'downloadParticipantSheet=true',
                'icon'       => 'bundles/markocupicrszjahresprogramm/excel.svg',
                'attributes' => 'onclick="if (!confirm(\'Soll die Teilnehmerliste heruntergeladen werden?\')) return false; Backend.getScrollOffset();"',
            ],
        ],
    ],
    'palettes'    => [
        '__selector__' => ['autoSignIn'],
        'default'      => '
        {Zeit},kw,start_date,end_date;
        {Beschreibung},art,ort,zeit,teilnehmer,trainer;
        {erweiterte Angaben:hide},wettkampfform,phase,trainingsstunden,kommentar;
        {registration_legend},autoSignIn
        ',
    ],
    'subpalettes' => [
        'autoSignIn' => 'registrationStop,autoSignInKategories',
    ],
    'fields'      => [
        'id'                   => [
            'search' => true,
            'sql'    => "int(10) unsigned NOT NULL auto_increment",
        ],
        'uniqueId'             => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['readonly' => true, 'doNotShow' => true, 'doNotCopy' => true, 'unique' => true],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'registrationStop'     => [
            'exclude'   => true,
            'inputType' => 'text',
            'default'   => time(),
            'eval'      => ['rgxp' => 'date', 'mandatory' => true, 'datepicker' => true, 'tl_class' => 'clr wizard'],
            'sql'       => "int(10) unsigned NOT NULL default '0'",
        ],
        'autoSignIn'           => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'search'    => true,
            'sorting'   => true,
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr'],
            'sql'       => "varchar(1) NOT NULL default ''",
        ],
        'autoSignInKategories' => [
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => array_keys(System::getContainer()->getParameter('rsz_benutzerverwaltung.wettkampfkategorien')),
            'eval'      => ['multiple' => true, 'chosen' => true, 'mandatory' => true, 'tl_class' => 'clr'],
            'sql'       => "blob NULL",
        ],
        'tstamp'               => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'start_date'           => [
            'exclude'   => true,
            'inputType' => 'text',
            'default'   => time(),
            'search'    => true,
            'sorting'   => true,
            'eval'      => ['mandatory' => true, 'datepicker' => true, 'rgxp' => 'date'],
            'sql'       => "int(10) unsigned NOT NULL default '0'",
        ],
        'end_date'             => [
            'exclude'   => true,
            'inputType' => 'text',
            'default'   => time(),
            'search'    => true,
            'sorting'   => true,
            'eval'      => ['mandatory' => true, 'datepicker' => true, 'rgxp' => 'date'],
            'sql'       => "int(10) unsigned NOT NULL default '0'",
        ],
        'art'                  => [
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => explode(',', Config::get('tl_rsz_jahresprogramm_art')),
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => ['includeBlankOption' => true, 'mandatory' => true, 'maxlength' => 64],
            'sql'       => "varchar(512) NOT NULL default ''",
        ],
        'trainer'              => [
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => ['maxlength' => 64,],
            'sql'       => "varchar(512) NOT NULL default ''",
        ],
        'teilnehmer'           => [
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => explode(',', Config::get('mcupic_be_benutzerverwaltung_trainingsgruppe')),
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => ['includeBlankOption' => true, 'chosen' => true, 'multiple' => true, 'maxlength' => 255],
            'sql'       => "blob NULL",
        ],
        'ort'                  => [
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => ['maxlength' => 64],
            'sql'       => "varchar(512) NOT NULL default ''",
        ],
        'zeit'                 => [
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'eval'      => ['maxlength' => 13],
            'sql'       => "varchar(512) NOT NULL default ''",
        ],
        'phase'                => [
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => ['maxlength' => 64],
            'sql'       => "varchar(512) NOT NULL default ''",
        ],
        'trainingsstunden'     => [
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => explode(',', Config::get('tl_jahresprogramm_trainingsstunden')),
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => ['maxlength' => 2],
            'sql'       => "varchar(3) NOT NULL default ''",
        ],
        'wettkampfform'        => [
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => explode(',', Config::get('tl_jahresprogramm_wettkampfform')),
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => ['includeBlankOption' => true, 'maxlength' => 64],
            'sql'       => "varchar(512) NOT NULL default ''",
        ],
        'treffpunkt'           => [
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => ['maxlength' => 64],
            'sql'       => "varchar(512) NOT NULL default ''",
        ],
        'kommentar'            => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'textarea',
            'eval'      => [],
            'sql'       => "mediumtext NULL",
        ],
        'kw'                   => [
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'eval'      => ['readonly' => true],
            'sql'       => "int(2) unsigned NOT NULL default '0'",
        ],
    ],
];
