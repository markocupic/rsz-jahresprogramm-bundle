<?php

declare(strict_types=1);

/*
 * This file is part of RSZ Jahresprogramm Bundle.
 *
 * (c) Marko Cupic 2023 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/rsz-jahresprogramm-bundle
 */

use Markocupic\RszJahresprogrammBundle\Model\RszJahresprogrammModel;
use Markocupic\RszJahresprogrammBundle\Model\RszJahresprogrammParticipantModel;

/*
 * Backend modules
 */
$GLOBALS['BE_MOD']['rsz_tools']['rsz_jahresprogramm'] = [
    'tables' => ['tl_rsz_jahresprogramm'],
];

/*
 * Models
 */
$GLOBALS['TL_MODELS']['tl_rsz_jahresprogramm'] = RszJahresprogrammModel::class;
$GLOBALS['TL_MODELS']['tl_rsz_jahresprogramm_participant'] = RszJahresprogrammParticipantModel::class;
