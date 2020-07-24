<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Table tl_rsz_jahresprogramm_participant
 */
$GLOBALS['TL_DCA']['tl_rsz_jahresprogramm_participant'] = array(

    // Config
    'config'      => array(
        'dataContainer'     => 'Table',
        'ptable'            => 'tl_member',
        'enableVersioning'  => true,
        'onsubmit_callback' => array(
            array('tl_rsz_jahresprogramm_participant', 'storeDateAdded'),
        ),
        'ondelete_callback' => array(//array('tl_rsz_jahresprogramm_participant', 'removeSession')
        ),
        'sql'               => array(
            'keys' => array(
                'id'    => 'primary',
                'pid' => 'index',
            ),
        ),
    ),
    // List
    'list'        => array(
        'sorting'           => array(
            'mode'        => 2,
            'fields'      => array('pid'),
            'flag'        => 1,
            'panelLayout' => 'filter;sort,search,limit',
        ),
        'label'             => array(
            'fields'         => array('pid', 'uniquePid'),
            'showColumns'    => true,
            'label_callback' => array('tl_rsz_jahresprogramm_participant', 'addIcon'),
        ),
        'global_operations' => array(
            'all' => array(
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ),
        ),
        'operations'        => array(
            'edit'   => array(
                'label' => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm_participant']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg',
            ),
            'delete' => array(
                'label'      => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm_participant']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ),
            'show'   => array(
                'label' => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm_participant']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg',
            ),

        ),
    ),
    // Palettes
    'palettes'    => array(
        'default' => 'addedOn;{personal_legend},member_id,signedOff,signedIn,signOffReason;',
    ),
    // Subpalettes
    'subpalettes' => array(//
    ),
    // Fields
    'fields'      => array(
        'id'            => array(
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ),
        'pid'           => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'uniquePid'         => array(
            'sql' => "varchar(32) NOT NULL default ''",
        ),
        'tstamp'        => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'addedOn'       => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm_participant']['addedOn'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array('rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'),
            'sql'       => "varchar(10) NOT NULL default ''",
        ),
        'signedOff'       => array(
            'sql' => "char(1) NOT NULL default ''",
        ),
        'signedIn'       => array(
            'sql' => "char(1) NOT NULL default ''",
        ),
        'signOffReason' => array(
            'label'       => &$GLOBALS['TL_LANG']['tl_rsz_jahresprogramm_participant']['signOffReason'],
            'exclude'     => true,
            'search'      => true,
            'inputType'   => 'textarea',
            'eval'        => array('rte' => 'tinyMCE'),
            'explanation' => 'insertTags',
            'sql'         => "mediumtext NULL",
        )
    )
);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class tl_rsz_jahresprogramm_participant extends Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * Store the date
     *
     * @param DataContainer $dc
     */
    public function storeDateAdded($dc)
    {
        // Front end call
        if (!($dc instanceof DataContainer))
        {
            return;
        }

        // Return if there is no active record (override all)
        if (!$dc->activeRecord || $dc->activeRecord->addedOn > 0)
        {
            return;
        }
        $time = time();

        $this->Database->prepare("UPDATE tl_rsz_jahresprogramm_participant SET addedOn=? WHERE id=?")->execute($time, $dc->id);
    }


}
