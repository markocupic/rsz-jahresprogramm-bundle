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

use Contao\Input;
use Contao\Config;
use Contao\System;
use Contao\StringUtil;
use League\Csv\Reader;
use League\Csv\Writer;
use Markocupic\RszJahresprogrammBundle\Model\RszJahresprogrammModel;
use Contao\DC_Table;
use Contao\DataContainer;

$GLOBALS['TL_DCA']['tl_rsz_jahresprogramm'] = [

    // Config
    'config'      => [
        'dataContainer'     => DC_Table::class,
        'enableVersioning'  => true,
        'sql'               => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
        'onload_callback'   => [
            [
                'tl_rsz_jahresprogramm',
                'setDca',
            ],
            [
                'tl_rsz_jahresprogramm',
                'setKalenderwocheToDb',
            ],
            [
                'tl_rsz_jahresprogramm',
                'adjustEndDate',
            ],
            [
                'tl_rsz_jahresprogramm',
                'insertUniqueId',
            ],
            [
                'tl_rsz_jahresprogramm',
                'checkReferantialIntegrity',
            ],
        ],
        'ondelete_callback' => [
            [
                'tl_rsz_jahresprogramm',
                'delPraesenzkontrolle',
            ],
        ],
    ],
    // List
    'list'        => [
        'sorting'           => [
            'mode'            => DataContainer::MODE_SORTED,
            'fields'          => ['start_date'],
            'flag'            => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout'     => 'filter;sort,search,limit',
            'disableGrouping' => true,
        ],
        'label'             => [
            'fields'         => [
                'kw',
                'art',
                'teilnehmer',
            ],
            'format'         => '#STATUS# <span style="color:green;">KW %s, #datum#</span><span style="padding-left:10px; color:blue;">%s</span><span style="padding-left:10px;color:red;">[%s]</span> #signIn#',
            'label_callback' => [
                'tl_rsz_jahresprogramm',
                'labelCallback',
            ],
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
                'label' => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'        => [
                'label' => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete'      => [
                'label'      => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\')) return false; Backend.getScrollOffset();"',
            ],
            'show'        => [
                'label' => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
            'participant' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['participants'],
                'href'       => 'downloadParticipantSheet=true',
                'icon'       => 'bundles/markocupicrszjahresprogramm/excel.svg',
                'attributes' => 'onclick="if (!confirm(\'Soll die Teilnehmerliste heruntergeladen werden?\')) return false; Backend.getScrollOffset();"',
            ],
        ],
    ],
    // Palettes
    'palettes'    => [
        '__selector__' => ['autoSignIn'],
        'default'      => '{Zeit},kw,start_date,end_date;{Beschreibung},art,ort,zeit,teilnehmer,trainer;{erweiterte Angaben:hide},wettkampfform,phase,trainingsstunden,kommentar;{registration_legend},autoSignIn',
    ],
    'subpalettes' => [
        'autoSignIn' => 'registrationStop,autoSignInKategories',
    ],
    // Fields
    'fields'      => [
        'id'                   => [
            'search' => true,
            'sql'    => "int(10) unsigned NOT NULL auto_increment",
        ],
        // Important: foreignkey for tl_jahreprogramm_participant
        'uniqueId'             => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'readonly'  => true,
                'doNotShow' => true,
                'doNotCopy' => true,
                'unique'    => true,
            ],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'registrationStop'     => [
            'exclude'   => true,
            'inputType' => 'text',
            'default'   => time(),
            'eval'      => [
                'rgxp'       => 'date',
                'mandatory'  => true,
                'datepicker' => true,
                'tl_class'   => 'clr wizard',
            ],
            'sql'       => "int(10) unsigned NOT NULL default '0'",
        ],
        'autoSignIn'           => [
            'exclude'   => true,
            'inputType' => 'checkbox',
            'search'    => true,
            'sorting'   => true,
            'eval'      => [
                'submitOnChange' => true,
                'tl_class'       => 'clr',
            ],
            'sql'       => "varchar(1) NOT NULL default ''",
        ],
        'autoSignInKategories' => [
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => array_keys(System::getContainer()->getParameter('rsz_benutzerverwaltung.wettkampfkategorien')),
            'eval'      => [
                'multiple'  => true,
                'chosen'    => true,
                'mandatory' => true,
                'tl_class'  => 'clr',
            ],
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
            'eval'      => [
                'mandatory'  => true,
                'datepicker' => true,
                'rgxp'       => 'date',
            ],
            'sql'       => "int(10) unsigned NOT NULL default '0'",
        ],
        'end_date'             => [
            'exclude'   => true,
            'inputType' => 'text',
            'default'   => time(),
            'search'    => true,
            'sorting'   => true,
            'eval'      => [
                'mandatory'  => true,
                'datepicker' => true,
                'rgxp'       => 'date',
            ],
            'sql'       => "int(10) unsigned NOT NULL default '0'",
        ],
        'art'                  => [
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => explode(',', Config::get('tl_rsz_jahresprogramm_art')),
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => [
                'includeBlankOption' => true,
                'mandatory'          => true,
                'maxlength'          => 64,
            ],
            'sql'       => "varchar(512) NOT NULL default ''",
        ],
        'trainer'              => [
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => [
                'mandatory' => false,
                'maxlength' => 64,
            ],
            'sql'       => "varchar(512) NOT NULL default ''",
        ],
        'teilnehmer'           => [
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => explode(',', Config::get('mcupic_be_benutzerverwaltung_trainingsgruppe')),
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => [
                'includeBlankOption' => true,
                'chosen'             => true,
                'multiple'           => true,
                'mandatory'          => false,
                'maxlength'          => 255,
            ],
            'sql'       => "blob NULL",
        ],
        'ort'                  => [
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => [
                'mandatory' => false,
                'maxlength' => 64,
            ],
            'sql'       => "varchar(512) NOT NULL default ''",
        ],
        'zeit'                 => [
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'eval'      => [
                'mandatory' => false,
                'maxlength' => 13,
            ],
            'sql'       => "varchar(512) NOT NULL default ''",
        ],
        'phase'                => [
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => [
                'mandatory' => false,
                'maxlength' => 64,
            ],
            'sql'       => "varchar(512) NOT NULL default ''",
        ],
        'trainingsstunden'     => [
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => explode(',', Config::get('tl_jahresprogramm_trainingsstunden')),
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => [
                'mandatory' => false,
                'maxlength' => 2,
            ],
            'sql'       => "varchar(3) NOT NULL default ''",
        ],
        'wettkampfform'        => [
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => explode(',', Config::get('tl_jahresprogramm_wettkampfform')),
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => [
                'includeBlankOption' => true,
                'mandatory'          => false,
                'maxlength'          => 64,
            ],
            'sql'       => "varchar(512) NOT NULL default ''",
        ],
        'treffpunkt'           => [
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => [
                'mandatory' => false,
                'maxlength' => 64,
            ],
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
            'eval'      => ['style' => '" disabled="disabled'],
            'sql'       => "int(2) unsigned NOT NULL default '0'",
        ],
    ],
];

class tl_rsz_jahresprogramm extends Backend
{

    /**
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');

        // Download the participant sheet as csv file **Dez 2016**
        if (Input::get('downloadParticipantSheet')) {
            $this->downloadParticipantSheet();
        }
    }

    /**
     * @throws Exception
     */
    protected function downloadParticipantSheet(): void
    {
        $objEvent = RszJahresprogrammModel::findByPk(Input::get('id'));
        if ($objEvent === null) {
            throw new Exception('Event not found!');
        }

        $arrHeadline = [];
        $arrHeadline['headline'] = [
            'Name',
            'nimmt teil',
            'nimmt nicht teil',
            'Grund für Nichtteilnahme',
            'Zeitstempel',
        ];

        // Auto Sign In
        $arrAutoSignIn = [];
        if ($objEvent->autoSignIn) {
            $arrKategories = StringUtil::deserialize($objEvent->autoSignInKategories, true);
            $objUser = $this->Database->prepare("SELECT * FROM tl_user ORDER BY kategorie")->execute();
            while ($objUser->next()) {
                $arrFunktion = StringUtil::deserialize($objUser->funktion, true);
                if (in_array('Athlet', $arrFunktion)) {
                    if (in_array($objUser->kategorie, $arrKategories)) {
                        $arrAutoSignIn[$objUser->username][] = utf8_decode($objUser->name);
                        $arrAutoSignIn[$objUser->username][] = '1';
                        $arrAutoSignIn[$objUser->username][] = '';
                        $arrAutoSignIn[$objUser->username][] = '';
                        $arrAutoSignIn[$objUser->username][] = '';
                    }
                }
            }
        }

        // Manual registration via Frontend Module "Jahresplanung"
        $arrSignIn = [];
        $objParticipant = $this->Database->prepare("SELECT * FROM tl_rsz_jahresprogramm_participant WHERE tl_rsz_jahresprogramm_participant.uniquePid=(SELECT uniqueId FROM tl_rsz_jahresprogramm WHERE tl_rsz_jahresprogramm.id=?)")
            ->execute(Input::get('id'));

        while ($objParticipant->next()) {
            $item = [];
            $item[] = utf8_decode(\Contao\MemberModel::findByPk($objParticipant->pid)->firstname).' '.utf8_decode(\Contao\MemberModel::findByPk($objParticipant->pid)->lastname);
            $item[] = $objParticipant->signedIn;
            $item[] = $objParticipant->signedOff;
            $item[] = utf8_decode(
                str_replace([
                    "\r\n",
                    "\r",
                    "\n",
                ], " ", $objParticipant->signOffReason)
            );
            $item[] = \Contao\Date::parse('Y-m-d', $objParticipant->tstamp);
            $arrSignIn[\Contao\MemberModel::findByPk($objParticipant->pid)->username] = $item;
        }

        // Merging the arrays
        $arrRows = array_merge($arrHeadline, $arrAutoSignIn, $arrSignIn);

        // Create temporary file
        $tmp = 'system/tmp/rsz-event-teilnehmerliste_event-'.\Contao\Date::parse('Y-m-d', $objEvent->start_date).'.csv';
        $objFile = new \Contao\File($tmp);
        $objFile->write('');

        // Convert special chars
        $arrFinal = [];
        foreach ($arrRows as $arrRow) {
            $arrLine = array_map(function ($v) {
                return html_entity_decode(htmlspecialchars_decode((string)$v));
            }, $arrRow);
            $arrFinal[] = $arrLine;
        }

        // Load the CSV document from a string
        $csv = Writer::createFromString('');
        $csv->setOutputBOM(Reader::BOM_UTF8);
        $csv->setDelimiter(';');
        $csv->setEnclosure('"');

        // Insert all the records
        $csv->insertAll($arrFinal);

        // Write content into file
        $objFile->write($csv);
        $objFile->close();
        $objFile->sendToBrowser($objFile->name);
    }

    /**
     * Ondelete callback
     * @param DataContainer $dc
     */
    public function delPraesenzkontrolle(DataContainer $dc): void
    {
        $objDb = $this->Database->prepare('SELECT * FROM tl_rsz_praesenzkontrolle WHERE pid=?')->execute($dc->id);

        while ($objDb->next()) {
            $objDel = $this->Database->prepare('DELETE FROM tl_rsz_praesenzkontrolle WHERE id=?')->execute($objDb->id);
            if ($objDel->affectedRows) {
                $this->log('DELETE FROM tl_rsz_praesenzkontrolle WHERE id='.$objDb->id, __CLASS__.' '.__FUNCTION__.'()', TL_GENERAL);
            }
        }
    }

    /**
     * Onload callback
     * Manipulate dca
     */
    public function setDca(): void
    {
        /** Do some restrictions to default users */
        if (!$this->User->hasAccess('rszjahresprogrammp', 'create')) {
            unset($GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['list']['operations']['edit']);
            unset($GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['list']['operations']['copy']);
            unset($GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['list']['operations']['delete']);

            $GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['config']['closed'] = true;
            $GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['config']['notEditable'] = true;
            $GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['config']['notDeletable'] = true;
            $GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['config']['notSortable'] = true;
            $GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['config']['notCreatable'] = true;
            $GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['config']['notCopyable'] = true;
        }
    }

    /**
     * Onload callback
     * Erstellt anhand des startDatums die Kalenderwoche des Datensatzes
     */
    public function setKalenderwocheToDb(): void
    {
        $date = $this->Database->prepare("SELECT start_date,id FROM tl_rsz_jahresprogramm")->execute();
        while ($row = $date->next()) {
            if ($row->start_date == "") {
                return;
            }
            $setKw = $this->Database->prepare("UPDATE tl_rsz_jahresprogramm SET kw = ? WHERE id = ?");
            $setKw->execute(\Contao\Date::parse("W", $row->start_date), $row->id);
        }
    }

    /**
     * Onload callback
     */
    public function adjustEndDate(DataContainer $dc): void
    {
        // Wenn für das End-Datum nichts angegeben wird, wird dafür automatisch das Start-Datum eingetragen
        $date = $this->Database->prepare("SELECT id, start_date FROM tl_rsz_jahresprogramm WHERE (start_date != ? AND end_date=?) OR end_date < start_date")->execute(0, 0);

        while ($date->next()) {
            $end_date = $this->Database->prepare("UPDATE tl_rsz_jahresprogramm SET end_date = ? WHERE id = ?");
            $end_date->execute($date->start_date, $date->id);
        }
    }

    /**
     * Onload callback
     * Important for tl_rsz_jahresprogramm_participant
     */
    public function insertUniqueId(): void
    {
        // Wenn das End-Datum leer ist, wird automatisch das Start-Datum eingesetzt
        $objDb = $this->Database->prepare("SELECT * FROM tl_rsz_jahresprogramm WHERE uniqueId = ''")->execute();

        while ($objDb->next()) {
            $this->Database->prepare("UPDATE tl_rsz_jahresprogramm SET uniqueId = ? WHERE id = ?")->execute(uniqid($objDb->id), $objDb->id);
        }
    }

    /**
     * Onload callback
     * Delete entries in tl_rsz_jahresprogramm_participant
     * that have no foreign key constraints
     */
    public function checkReferantialIntegrity(): void
    {

        $this->Database->execute("DELETE FROM tl_rsz_jahresprogramm_participant WHERE NOT EXISTS (SELECT * FROM tl_rsz_jahresprogramm WHERE tl_rsz_jahresprogramm.uniqueId = tl_rsz_jahresprogramm_participant.uniquePid)");
    }

    /**
     * Label callback
     */
    public function labelCallback(array $row, string $label): string
    {
        $label = str_replace('#datum#', \Contao\Date::parse('Y-m-d', (int)$row['start_date']), $label);

        $this->Database->prepare('SELECT start_date,trainers FROM tl_rsz_praesenzkontrolle WHERE id=?')->execute($row['id']);

        if (time() > $row['start_date']) {
            $status = '<div style="display:inline; padding-right:4px;"><img src="bundles/markocupicrszjahresprogramm/check.svg" alt="history" title="abgelaufen"></div>';
        } else {
            $status = '<div style="display:inline; padding-right:0;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>';
        }

        $label = str_replace('#STATUS#', $status, $label);

        return str_replace('#signIn#', $row['autoSignIn'] ? 'Anmeldung bis: '.\Contao\Date::parse('Y-m-d', $row['registrationStop']) : '', $label);
    }
}


