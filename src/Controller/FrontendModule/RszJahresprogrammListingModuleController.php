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
use Contao\Database;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use Markocupic\ExportTable\Config\Config;
use Markocupic\ExportTable\Export\ExportTable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(RszJahresprogrammListingModuleController::TYPE, category:'rsz_frontend_modules', template: 'mod_rsz_jahresprogramm_listing')]
class RszJahresprogrammListingModuleController extends AbstractFrontendModuleController
{
    public const TYPE = 'rsz_jahresprogramm_listing_module';

    private ContaoFramework $framework;
    private ExportTable $exportTable;
    private ?PageModel $page = null;

    public function __construct(ContaoFramework $framework, ExportTable $exportTable)
    {
        $this->framework = $framework;
        $this->exportTable = $exportTable;
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

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $stringUtilAdapter = $this->framework->getAdapter(StringUtil::class);
        $databaseAdapter = $this->framework->getAdapter(Database::class);

        // Download link
        $template->downloadUrl = $this->page->getFrontendUrl().'?act=downloadJahresprogrammXls';

        // Die ganze Tabelle
        $objJumpTo = PageModel::findByPk($model->rszJahresprogrammReaderPage);
        $arrJahresprogramm = [];
        $objJahresprogramm = $databaseAdapter->getInstance()
            ->execute('SELECT * FROM tl_rsz_jahresprogramm ORDER BY start_date ASC')
        ;

        while ($objJahresprogramm->next()) {
            $arrJahresprogramm[] = [
                'id' => $objJahresprogramm->id,
                'kw' => $objJahresprogramm->kw,
                'start_date' => date('Y-m-d', (int) $objJahresprogramm->start_date),
                'end_date' => date('Y-m-d', (int) $objJahresprogramm->end_date),
                'tstamp_end_date' => $objJahresprogramm->end_date,
                'art' => $objJahresprogramm->art,
                'kommentar' => $objJahresprogramm->kommentar,
                'ort' => $stringUtilAdapter->substr($objJahresprogramm->ort, 40),
                'trainer' => $objJahresprogramm->trainer,
                'autoSignIn' => $objJahresprogramm->autoSignIn,
                'signInStop' => $objJahresprogramm->autoSignIn ? date('Y-m-d', (int) $objJahresprogramm->registrationStop) : '',
                'jumpTo' => $objJumpTo ? $objJumpTo->getFrontendUrl('/'.$objJahresprogramm->id) : '',
            ];
        }

        $template->Jahresprogramm = $arrJahresprogramm;

        // Next events
        $arrNextEvent = [];
        $objJahresprogramm = $databaseAdapter->getInstance()
            ->prepare('SELECT * FROM tl_rsz_jahresprogramm WHERE start_date > ? ORDER BY start_date, id')
            ->limit(4)
            ->execute(time())
        ;

        while ($objJahresprogramm->next()) {
            $arrNextEvent[] = [
                'id' => $objJahresprogramm->id,
                'kw' => $objJahresprogramm->kw,
                'start_date' => date('Y-m-d', (int) $objJahresprogramm->start_date),
                'end_date' => date('Y-m-d', (int) $objJahresprogramm->end_date),
                'tstamp_end_date' => $objJahresprogramm->end_date,
                'art' => $objJahresprogramm->art,
                'kommentar' => $objJahresprogramm->kommentar,
                'ort' => $stringUtilAdapter->substr($objJahresprogramm->ort, 40),
                'trainer' => $stringUtilAdapter->substr($objJahresprogramm->trainer, 10),
                'autoSignIn' => $objJahresprogramm->autoSignIn,
                'signInStop' => $objJahresprogramm->autoSignIn ? date('Y-m-d', (int) $objJahresprogramm->registrationStop) : '',
                'jumpTo' => $objJumpTo ? $objJumpTo->getFrontendUrl('/'.$objJahresprogramm->id) : '',
            ];
        }
        $template->arrNextEvent = $arrNextEvent;

        return $template->getResponse();
    }
}
