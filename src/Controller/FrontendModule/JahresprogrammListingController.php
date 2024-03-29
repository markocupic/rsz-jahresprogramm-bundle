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

namespace Markocupic\RszJahresprogrammBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Database;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Markocupic\ExportTable\Config\Config;
use Markocupic\ExportTable\Export\ExportTable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(JahresprogrammListingController::TYPE, category:'rsz_frontend_modules')]
class JahresprogrammListingController extends AbstractFrontendModuleController
{
    public const TYPE = 'jahresprogramm_listing';

    private ?PageModel $page = null;

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly ExportTable $exportTable,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        $inputAdapter = $this->framework->getAdapter(Input::class);

        // Get the page model
        $this->page = $page;

        if ('downloadJahresprogrammXls' === $inputAdapter->get('act')) {
            $this->downloadJahresprogrammXls();
        }

        return parent::__invoke($request, $model, $section, $classes);
    }

    /**
     * @throws \Exception
     */
    public function downloadJahresprogrammXls(): void
    {
        /** @var Database $databaseAdapter */
        $databaseAdapter = $this->framework->getAdapter(Database::class);

        $arrFields = $databaseAdapter->getInstance()->getFieldNames('tl_rsz_jahresprogramm');

        // Exclude dome fields
        $arrExclude = ['id', 'tstamp', 'kw', 'uniqueId'];
        $arrFields = array_diff($arrFields, $arrExclude);

        // Config
        $config = (new Config('tl_rsz_jahresprogramm'))
            ->setFields($arrFields)
            ->setSortBy('start_date')
            ->setSortDirection('ASC')
            ->setRowCallback(
                static fn ($arrRow) => array_map(static fn ($varValue) => \is_string($varValue) ? iconv('UTF-8', 'ISO-8859-1', $varValue) : $varValue, $arrRow)
            )
        ;

        $this->exportTable->run($config);
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $stringUtilAdapter = $this->framework->getAdapter(StringUtil::class);
        $databaseAdapter = $this->framework->getAdapter(Database::class);

        // Download link
        $template->set('downloadUrl', $this->page->getFrontendUrl().'?act=downloadJahresprogrammXls');

        // Die ganze Tabelle
        $objJumpTo = PageModel::findByPk($model->rszJahresprogrammReaderPage);
        $eventsAll = [];
        $objJahresprogramm = $databaseAdapter
            ->getInstance()
            ->execute('SELECT * FROM tl_rsz_jahresprogramm ORDER BY start_date ASC')
        ;

        while ($objJahresprogramm->next()) {
            $eventsAll[] = [
                'id' => $objJahresprogramm->id,
                'kw' => $objJahresprogramm->kw,
                'start_date' => date('Y-m-d', (int) $objJahresprogramm->start_date),
                'end_date' => date('Y-m-d', (int) $objJahresprogramm->end_date),
                'tstamp_end_date' => $objJahresprogramm->end_date,
                'art' => $objJahresprogramm->art,
                'kommentar' => $objJahresprogramm->kommentar,
                'ort' => $stringUtilAdapter->substr($objJahresprogramm->ort, 40),
                'trainer' => $objJahresprogramm->trainer,
                'auto_sign_in' => $objJahresprogramm->autoSignIn,
                'sign_in_stop' => $objJahresprogramm->autoSignIn ? date('Y-m-d', (int) $objJahresprogramm->registrationStop) : '',
                'jump_to' => $objJumpTo ? $objJumpTo->getFrontendUrl('/'.$objJahresprogramm->id) : '',
            ];
        }

        $template->set('events_all', $eventsAll);

        // Next events
        $upcomingEvents = [];
        $objJahresprogramm = $databaseAdapter->getInstance()
            ->prepare('SELECT * FROM tl_rsz_jahresprogramm WHERE start_date > ? ORDER BY start_date, id')
            ->limit(4)
            ->execute(time())
        ;

        while ($objJahresprogramm->next()) {
            $upcomingEvents[] = [
                'id' => $objJahresprogramm->id,
                'kw' => $objJahresprogramm->kw,
                'start_date' => date('Y-m-d', (int) $objJahresprogramm->start_date),
                'end_date' => date('Y-m-d', (int) $objJahresprogramm->end_date),
                'tstamp_end_date' => $objJahresprogramm->end_date,
                'art' => $objJahresprogramm->art,
                'kommentar' => $objJahresprogramm->kommentar,
                'ort' => $stringUtilAdapter->substr($objJahresprogramm->ort, 40),
                'trainer' => $stringUtilAdapter->substr($objJahresprogramm->trainer, 10),
                'auto_sign_in' => $objJahresprogramm->autoSignIn,
                'sign_in_stop' => $objJahresprogramm->autoSignIn ? date('Y-m-d', (int) $objJahresprogramm->registrationStop) : '',
                'jump_to' => $objJumpTo ? $objJumpTo->getFrontendUrl('/'.$objJahresprogramm->id) : '',
            ];
        }
        $template->set('upcoming_events', $upcomingEvents);

        return $template->getResponse();
    }
}
