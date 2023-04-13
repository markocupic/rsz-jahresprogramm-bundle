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

namespace Markocupic\RszJahresprogrammBundle\DataContainer;

use Contao\Backend;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\DataContainer;
use Contao\Date;
use Contao\File;
use Contao\MemberModel;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\Reader;
use League\Csv\Writer;
use Markocupic\RszJahresprogrammBundle\Model\RszJahresprogrammModel;
use Markocupic\RszJahresprogrammBundle\Security\RszBackendPermissions;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class RszJahresprogramm extends Backend
{
    public function __construct(
        private readonly Connection $connection,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly ?LoggerInterface $contaoGeneralLogger,
    ) {
        parent::__construct();
    }

    #[AsCallback(table: 'tl_rsz_jahresprogramm', target: 'config.ondelete', priority: 255)]
    public function deletePraesenzkontrolle(DataContainer $dc): void
    {
        $rows = $this->connection->fetchAllAssociative('SELECT id FROM tl_rsz_praesenzkontrolle WHERE pid = ?', [$dc->id]);

        foreach ($rows as $row) {
            $intAffected = $this->connection->delete('tl_rsz_praesenzkontrolle', ['pid' => $row['id']]);

            if ($intAffected) {
                $this?->contaoGeneralLogger->info(
                    'DELETE FROM tl_rsz_praesenzkontrolle WHERE id ='.$row['id'],
                    ['contao' => new ContaoContext(__METHOD__, 'DELETE_PRAESENZKONTROLLE')],
                );
            }
        }
    }

    #[AsCallback(table: 'tl_rsz_jahresprogramm', target: 'config.onload', priority: 255)]
    public function setDca(): void
    {
        /* Do some restrictions to default users */
        if (!$this->security->isGranted(RszBackendPermissions::USER_CAN_EDIT_RSZ_JAHRESPROGRAMM, 'create')) {
            unset($GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['list']['operations']['edit'], $GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['list']['operations']['copy'], $GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['list']['operations']['delete']);

            $GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['config']['closed'] = true;
            $GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['config']['notEditable'] = true;
            $GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['config']['notDeletable'] = true;
            $GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['config']['notSortable'] = true;
            $GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['config']['notCreatable'] = true;
            $GLOBALS['TL_DCA']['tl_rsz_jahresprogramm']['config']['notCopyable'] = true;
        }
    }

    #[AsCallback(table: 'tl_rsz_jahresprogramm', target: 'config.onload', priority: 255)]
    public function setKalenderwocheToDb(): void
    {
        $events = $this->connection->fetchAllAssociative('SELECT start_date,id FROM tl_rsz_jahresprogramm');

        foreach ($events as $event) {
            if ('' === $event['start_date']) {
                continue;
            }
            $set = [
                'kw' => Date::parse('W', $event['start_date']),
            ];

            $this->connection->update('tl_rsz_jahresprogramm', $set, ['id' => $event['id']]);
        }
    }

    #[AsCallback(table: 'tl_rsz_jahresprogramm', target: 'config.onload', priority: 255)]
    public function adjustEndDate(DataContainer $dc): void
    {
        // If nothing is specified for the end date, the start date is automatically entered instead.
        $events = $this->connection->fetchAllAssociative('SELECT id, start_date FROM tl_rsz_jahresprogramm WHERE (start_date != ? AND end_date = ?) OR end_date < start_date', [0, 0]);

        foreach ($events as $event) {
            $set = [
                'end_date' => $event['start_date'],
            ];
            $this->connection->update('tl_rsz_jahresprogramm', $set, ['id' => $event['id']]);
        }
    }

    #[AsCallback(table: 'tl_rsz_jahresprogramm', target: 'config.onload', priority: 255)]
    public function insertUniqueId(): void
    {
        $events = $this->connection->fetchAllAssociative('SELECT * FROM tl_rsz_jahresprogramm WHERE uniqueId = ?', ['']);

        foreach ($events as $event) {
            $set = [
                'uniqueId' => uniqid($event['id']),
            ];
            $this->connection->update('tl_rsz_jahresprogramm', $set, ['id' => $event['id']]);
        }
    }

    /**
     * Delete entries in tl_rsz_jahresprogramm_participant
     * that have no foreign key constraints.
     */
    #[AsCallback(table: 'tl_rsz_jahresprogramm', target: 'config.onload', priority: 255)]
    public function checkReferentialIntegrity(): void
    {
        $this->connection->executeStatement('DELETE FROM tl_rsz_jahresprogramm_participant WHERE NOT EXISTS (SELECT * FROM tl_rsz_jahresprogramm WHERE tl_rsz_jahresprogramm.uniqueId = tl_rsz_jahresprogramm_participant.uniquePid)');
    }

    /**
     * @throws CannotInsertRecord
     * @throws Exception
     * @throws InvalidArgument
     * @throws \Doctrine\DBAL\Exception
     */
    #[AsCallback(table: 'tl_rsz_jahresprogramm', target: 'config.onload', priority: 255)]
    public function downloadParticipantSheet(): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request->query->has('downloadParticipantSheet')) {
            return;
        }

        $id = $request->query->get('id');

        $objEvent = RszJahresprogrammModel::findByPk($id);

        if (null === $objEvent) {
            throw new \Exception(sprintf('Event with ID %d not found!', $id));
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
            $arrCategories = StringUtil::deserialize($objEvent->autoSignInKategories, true);

            $users = $this->connection->fetchAllAssociative('SELECT * FROM tl_user ORDER BY kategorie');

            foreach ($users as $user) {
                $arrFunktion = StringUtil::deserialize($user['funktion'], true);

                if (\in_array('Athlet', $arrFunktion, true)) {
                    if (\in_array($user['kategorie'], $arrCategories, true)) {
                        $arrAutoSignIn[$user['username']][] = utf8_decode((string) $user['name']);
                        $arrAutoSignIn[$user['username']][] = '1';
                        $arrAutoSignIn[$user['username']][] = '';
                        $arrAutoSignIn[$user['username']][] = '';
                        $arrAutoSignIn[$user['username']][] = '';
                    }
                }
            }
        }

        // Manual registration via Frontend Module "Jahresplanung"
        $arrSignIn = [];
        $registrations = $this->connection->fetchAllAssociative('SELECT * FROM tl_rsz_jahresprogramm_participant WHERE tl_rsz_jahresprogramm_participant.uniquePid=(SELECT uniqueId FROM tl_rsz_jahresprogramm WHERE tl_rsz_jahresprogramm.id = ?)', [$id]);

        foreach ($registrations as $registration) {
            $item = [];
            $item[] = utf8_decode(MemberModel::findByPk($registration['pid'])->firstname).' '.utf8_decode(MemberModel::findByPk($registration['pid'])->lastname);
            $item[] = $registration['signedIn'];
            $item[] = $registration['signedOff'];
            $item[] = utf8_decode(
                str_replace([
                    "\r\n",
                    "\r",
                    "\n",
                ], ' ', $registration['signOffReason'])
            );
            $item[] = Date::parse('Y-m-d', $registration['tstamp']);
            $arrSignIn[MemberModel::findByPk($registration['pid'])->username] = $item;
        }

        // Merging the arrays
        $arrRows = array_merge($arrHeadline, $arrAutoSignIn, $arrSignIn);

        // Convert special chars
        $arrFinal = [];

        foreach ($arrRows as $arrRow) {
            $arrLine = array_map(static fn ($v) => html_entity_decode(htmlspecialchars_decode((string) $v)), $arrRow);
            $arrFinal[] = $arrLine;
        }

        // Load the CSV document from a string
        $csv = Writer::createFromString('');
        $csv->setOutputBOM(Reader::BOM_UTF8);
        $csv->setDelimiter(';');
        $csv->setEnclosure('"');

        // Insert all records
        $csv->insertAll($arrFinal);

        $filePath = 'system/tmp/rsz-event-teilnehmerliste_event-'.Date::parse('Y-m-d', $objEvent->start_date).'.csv';
        $objFile = new File($filePath);
        $objFile->write($csv->toString());
        $objFile->close();
        $objFile->sendToBrowser();
    }

    #[AsCallback(table: 'tl_rsz_jahresprogramm', target: 'list.label.label', priority: 255)]
    public function labelCallback(array $row, string $label): string
    {
        $label = str_replace('#datum#', Date::parse('Y-m-d', $row['start_date']), $label);

        if (time() > $row['start_date']) {
            $status = '<div style="display:inline; padding-right:4px;"><img src="bundles/markocupicrszjahresprogramm/check.svg" alt="history" title="abgelaufen"></div>';
        } else {
            $status = '<div style="display:inline; padding-right:0;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>';
        }

        $label = str_replace('#STATUS#', $status, $label);

        return str_replace('#signIn#', $row['autoSignIn'] ? 'Anmeldung bis: '.Date::parse('Y-m-d', $row['registrationStop']) : '', $label);
    }
}
