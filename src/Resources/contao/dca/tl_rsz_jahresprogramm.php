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
 * Table tl_rsz_jahresprogramm
 */
$GLOBALS['TL_DCA']['tl_rsz_jahresprogramm'] = [

    // Config
    'config'            => [
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id' => 'primary'
            ]
        ],
    ],
    'onload_callback'   => [
        [
            'tl_rsz_jahresprogramm',
            'setUpPalettes',
        ],
        [
            'tl_rsz_jahresprogramm',
            'setKalenderwocheToDb',
        ],
        [
            'tl_rsz_jahresprogramm',
            'hideEndDateWhenEmpty',
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
            'ondeleteCb_delPraesenzkontrolle',
        ],
    ],
    'edit'              => [
        'buttons_callback' => [
            //['tl_rsz_jahresprogramm', 'buttonsCallback']
        ]
    ],
    // List
    'list'              => [
        'sorting'           => [
            'mode'            => 1,
            'fields'          => ['start_date'],
            'flag'            => 1,
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
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
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
    'palettes'          => [
        '__selector__' => ['autoSignIn'],
        'default'      => '{Zeit}, kw, start_date, end_date; {Beschreibung},art, ort, zeit, teilnehmer, trainer; {erweiterte Angaben:hide}, wettkampfform, phase, trainingsstunden, kommentar;{registration_legend},autoSignIn;',
    ],
    'subpalettes'       => [
        'autoSignIn' => 'registrationStop,autoSignInKategories',
    ],
    // Fields
    'fields'            => [

        'id'       => [
            'label'  => ['ID'],
            'search' => true,
            'sql'    => "int(10) unsigned NOT NULL auto_increment",
        ],
        // Important: foreignkey for tl_jahreprogramm_participant
        'uniqueId' => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['uniqueId'],
            'inputType' => 'text',
            'eval'      => ['readonly' => true, 'doNotShow' => true, 'doNotCopy' => true, 'unique' => true],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],

        'registrationStop'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['registrationStop'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'date', 'mandatory' => true, 'datepicker' => true, 'tl_class' => 'clr wizard'],
            'sql'       => "int(10) unsigned NOT NULL default '0'",
        ],
        'autoSignIn'           => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['autoSignIn'],
            'inputType' => 'checkbox',
            'search'    => true,
            'sorting'   => true,
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr'],
            'sql'       => "varchar(1) NOT NULL default ''",
        ],
        'autoSignInKategories' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['autoSignInKategories'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => explode(',', $GLOBALS['TL_CONFIG']['mcupic_be_benutzerverwaltung_kategorie']),
            'eval'      => ['multiple' => true, 'chosen' => true, 'mandatory' => true, 'tl_class' => 'clr'],
            'sql'       => "varchar(1020) NOT NULL default ''",
        ],
        'tstamp'               => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'start_date'           => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['start_date'],
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
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['end_date'],
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
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['art'],
            'inputType' => 'select',
            'options'   => explode(',', $GLOBALS['TL_CONFIG']['tl_rsz_jahresprogramm_art']),
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => [
                'includeBlankOption' => true,
                'mandatory'          => true,
                'maxlength'          => 64,
            ],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'trainer'              => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['trainer'],
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => [
                'mandatory' => false,
                'maxlength' => 64,
            ],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'teilnehmer'           => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['teilnehmer'],
            'inputType' => 'select',
            'options'   => explode(',', $GLOBALS['TL_CONFIG']['mcupic_be_benutzerverwaltung_trainingsgruppe']),
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => [
                'includeBlankOption' => true,
                'chosen'             => true,
                'multiple'           => true,
                'mandatory'          => false,
                'maxlength'          => 255,
                'csv'                => ',',
            ],
            'sql'       => "varchar(512) NOT NULL default ''",
        ],
        'ort'                  => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['ort'],
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => [
                'mandatory' => false,
                'maxlength' => 64,
            ],
            'sql'       => "text NOT NULL",
        ],
        'zeit'                 => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['zeit'],
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'eval'      => [
                'mandatory' => false,
                'maxlength' => 13,
            ],
            'sql'       => "text NOT NULL",

        ],
        'phase'                => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['phase'],
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => [
                'mandatory' => false,
                'maxlength' => 64,
            ],
            'sql'       => "text NOT NULL",

        ],
        'trainingsstunden'     => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['trainingsstunden'],
            'inputType' => 'select',
            'options'   => explode(',', $GLOBALS['TL_CONFIG']['tl_rsz_jahresprogramm_trainingsstunden']),
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
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['wettkampfform'],
            'inputType' => 'select',
            'options'   => explode(',', $GLOBALS['TL_CONFIG']['tl_rsz_jahresprogramm_wettkampfform']),
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => [
                'includeBlankOption' => true,
                'mandatory'          => false,
                'maxlength'          => 64,
            ],
            'sql'       => "text NOT NULL",
        ],
        'treffpunkt'           => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['treffpunkt'],
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'eval'      => [
                'mandatory' => false,
                'maxlength' => 64,
            ],
            'sql'       => "text NOT NULL",
        ],
        'kommentar'            => [
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['kommentar'],
            'inputType' => 'textarea',
            'sql'       => "text NOT NULL",
        ],
        'kw'                   => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm']['kw'],
            'inputType' => 'text',
            'search'    => true,
            'sorting'   => true,
            'eval'      => ['style' => '" disabled="disabled'],
            'sql'       => "int(2) unsigned NOT NULL default '0'",
        ],
    ]
];
/**
 * @todo remove classname mess
 * @todo remote endete mess (Palette manipulator) 
 */
class tl_rsz_jahresprogramm extends Backend
{

    const TEMPORARY_FOLDER = 'system/tmp';

    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');

        // datum to string
        $sql = $this->Database->execute("SELECT * FROM tl_rsz_jahresprogramm");
        while ($sql->next())
        {
            if (strstr($sql->end_date, '.') && strstr($sql->start_date, '.'))
            {
                $start = mktime(0, 0, 0, substr($sql->start_date, 3, 2), substr($sql->start_date, 0, 2), substr($sql->start_date, 6, 4));
                $end = mktime(0, 0, 0, substr($sql->end_date, 3, 2), substr($sql->end_date, 0, 2), substr($sql->end_date, 6, 4));
                $set = [
                    "start_date" => $start,
                    "end_date"   => $end,
                ];
                $sqlUpd = $this->Database->prepare("UPDATE tl_rsz_jahresprogramm %s WHERE id=?")->set($set)->execute($sql->id);
            }
        }

        // Download the participant sheet as csv file **Dez 2016**
        if (\Input::get('downloadParticipantSheet'))
        {
            $objEvent = \Markocupic\RszJahresprogrammBundle\Model\RszJahresprogrammModel::findByPk(\Input::get('id'));
            if ($objEvent === null)
            {
                die('Event not found!');
            }

            $arrHeadline = [];
            $arrHeadline['headline'] = ['Name', 'nimmt teil', 'nimmt nicht teil', 'Grund fuer Nichtteilnahme', 'Zeitstempel'];

            // Auto Sign In
            $arrAutoSignIn = [];
            if ($objEvent->autoSignIn)
            {
                $arrKategories = deserialize($objEvent->autoSignInKategories, true);
                $objUser = $this->Database->prepare("SELECT * FROM tl_user ORDER BY kategorie")->execute();
                while ($objUser->next())
                {
                    $arrFunktion = deserialize($objUser->funktion, true);
                    if (in_array('Athlet', $arrFunktion))
                    {
                        if (in_array($objUser->kategorie, $arrKategories))
                        {
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
                ->execute(\Input::get('id'));
            while ($objParticipant->next())
            {
                $item = [];
                $item[] = utf8_decode(\MemberModel::findByPk($objParticipant->pid)->firstname) . ' ' . utf8_decode(\MemberModel::findByPk($objParticipant->pid)->lastname);
                $item[] = $objParticipant->signedIn;
                $item[] = $objParticipant->signedOff;
                $item[] = utf8_decode(str_replace(["\r\n", "\r", "\n"], " ", $objParticipant->signOffReason));
                $item[] = \Date::parse('Y-m-d', $objParticipant->tstamp);
                $arrSignIn[\MemberModel::findByPk($objParticipant->pid)->username] = $item;
            }

            // Merging the arrays
            $arrRows = array_merge($arrHeadline, $arrAutoSignIn, $arrSignIn);

            // Purge temporary folder
            $objFolder = new \Folder('files/tmp');
            $objFolder->purge();

            // Create temporary file
            $tmp = 'files/tmp/rsz-event-teilnehmerliste_event-' . \Date::parse('Ymd', $objEvent->start_date) . '.csv';
            new \Contao\Folder('files/tmp');
            $objFile = new \Contao\File($tmp);
            $objFile->write(''); // It is necessary to write en empty string into the file. Otherwise the file class return no handle.
            foreach ($arrRows as $row)
            {
                fputcsv($objFile->handle, $row, ";");
            }
            $objFile->close();

            // Send file to browser
            \Controller::sendFileToBrowser($tmp);
        }
    }

    /**
     * @param DataContainer $dc
     */
    public function ondeleteCb_delPraesenzkontrolle(DataContainer $dc)
    {
        $objDb = $this->Database->prepare('SELECT * FROM tl_rsz_praesenzkontrolle WHERE pid=?')->execute($dc->id);
        while ($objDb->next())
        {
            $objDel = $this->Database->prepare('DELETE FROM tl_rsz_praesenzkontrolle WHERE id=?')->execute($objDb->id);
            if ($objDel->affectedRows)
            {
                $this->log('DELETE FROM tl_rsz_praesenzkontrolle WHERE id=' . $objDb->id, __CLASS__ . ' ' . __FUNCTION__ . '()', TL_GENERAL);
            }
        }
    }

    /**
     *
     */
    public function setUpPalettes()
    {
        /** Do some restrictions to default users */
        if (!$this->User->isAdmin && !$this->User->isMemberOf(11))
        {
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
     * @param $row
     * @param $label
     * @return mixed
     */
    public function labelCallback($row, $label)
    {
        $label = str_replace('#datum#', date('Y-m-d', (int) $row['start_date']), $label);

        $mysql = $this->Database->prepare('SELECT start_date,trainers FROM tl_rsz_praesenzkontrolle WHERE id=?')->execute($row['id']);

        if (time() > $row['start_date'])
        {
            $status = '<div style="display:inline; padding-right:4px;"><img src="bundles/markocupicrszjahresprogramm/check.svg" alt="history" title="abgelaufen"></div>';
        }
        else
        {
            $status = '<div style="display:inline; padding-right:0px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>';
        }
        $label = str_replace('#STATUS#', $status, $label);

        $label = str_replace('#signIn#', $row['autoSignIn'] ? 'Anmeldung bis: ' . \Date('Y-m-d', $row['registrationStop']) : '', $label);

        return $label;
    }

    /**
     * erstellt anhand des startDatums in der db die Kalenderwoche in der der Anlass stattfindet
     */
    public function setKalenderwocheToDb()
    {
        $date = $this->Database->prepare("SELECT start_date,id FROM tl_rsz_jahresprogramm")->execute();
        while ($row = $date->next())
        {
            if ($row->start_date == "")
            {
                return;
            }
            $setKw = $this->Database->prepare("UPDATE tl_rsz_jahresprogramm SET kw = ? WHERE id = ?");
            $setKw->execute(date("W", $row->start_date), $row->id);
        }
    }

    public function hideEndDateWhenEmpty(DataContainer $dc)
    {
        //Wenn für das End-Datum nichts angegeben wird, wird dafür automatisch das Start-Datum eingetragen
        $date = $this->Database->prepare("SELECT id, start_date FROM tl_rsz_jahresprogramm WHERE start_date!='' AND end_date=''")->execute();
        while ($row = $date->next())
        {
            $end_date = $this->Database->prepare("UPDATE tl_rsz_jahresprogramm SET end_date = ? WHERE id = ?");
            $end_date->execute($row->start_date, $row->id);
        }
        //Wenn end-datum leer ist, wird es ausgeblendet, das ist der Fall beim Erstellen neuer Anlässe
        if ($dc->id != "")
        {
            $date = $this->Database->prepare("SELECT start_date FROM tl_rsz_jahresprogramm WHERE id = ?")->execute($dc->id);
            $date->fetchAssoc();
            if ($date->start_date == "")
            {
                $GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['palettes']['default'] = 'start_date, art, ort, wettkampfform; zeit, trainer; kommentar; phase, trainingsstunden';
            }
        }
    }

    /**
     * Important for tl_rsz_jahresprogramm_participant
     *@todo check phpdoc
     */
    public function insertUniqueId()
    {
        //Wenn für das End-Datum nichts angegeben wird, wird dafür automatisch das Start-Datum eingetragen
        $objDb = $this->Database->prepare("SELECT * FROM tl_rsz_jahresprogramm WHERE uniqueId = ''")->execute();
        while ($objDb->next())
        {
            $this->Database->prepare("UPDATE tl_rsz_jahresprogramm SET uniqueId = ? WHERE id = ?")->execute(uniqid($objDb->id), $objDb->id);
        }
    }

    /**
     * Delete entries in tl_rsz_jahresprogramm_participant that have no foreign key constraints
     */
    public function checkReferantialIntegrity()
    {
        $this->Database->prepare("SELECT * FROM tl_rsz_jahresprogramm")->execute();
        while ($objDb->next())
        {
            $arrUuid[] = $objDb->uniqueId;
        }
        $this->Database->execute("DELETE FROM tl_rsz_jahresprogramm_participant WHERE NOT EXISTS (SELECT * FROM tl_rsz_jahresprogramm WHERE tl_rsz_jahresprogramm.uniqueId = tl_rsz_jahresprogramm_participant.uniquePid)");
    }
}


