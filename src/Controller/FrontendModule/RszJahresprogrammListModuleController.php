<?php

/**
 * @copyright  Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    RSZ Jahresprogramm
 * @license    MIT
 * @see        https://github.com/markocupic/rsz-jahresprogramm-bundle
 *
 */

declare(strict_types=1);

namespace Markocupic\RszJahresprogrammBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Database;
use Contao\Date;
use Contao\FrontendUser;
use Contao\Input;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use Doctrine\DBAL\Connection;
use Markocupic\ExportTable\ExportTable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RszJahresprogrammListModuleController
 * @package Markocupic\RszJahresprogrammBundle\Controller\FrontendModule
 */
class RszJahresprogrammListModuleController extends AbstractFrontendModuleController
{
    /** @var PageModel */
    protected $page;

    /** @var  FrontendUser */
    protected $objUser;

    /**
     * RszJahresprogrammListModuleController constructor.
     */
    public function __construct()
    {
        //
    }

    /**
     * This method extends the parent __invoke method,
     * its usage is usually not necessary
     *
     * @param Request $request
     * @param ModuleModel $model
     * @param string $section
     * @param array|null $classes
     * @param PageModel|null $page
     * @return Response
     * @throws \Exception
     */
    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        /** @var Input $inputAdapter */
        $inputAdapter = $this->get('contao.framework')->getAdapter(Input::class);

        // Get the page model
        $this->page = $page;

        $user = $this->get('security.helper')->getUser();
        if ($user instanceof FrontendUser)
        {
            $this->objUser = $user;
        }

        if ($inputAdapter->get('act') === 'downloadJahresprogrammXls')
        {
            $this->downloadJahresprogrammXls();
        }

        return parent::__invoke($request, $model, $section, $classes);
    }

    /**
     * Lazyload some services
     * @return array
     */
    public static function getSubscribedServices(): array
    {
        $services = parent::getSubscribedServices();

        $services['contao.framework'] = ContaoFramework::class;
        $services['database_connection'] = Connection::class;
        $services['contao.routing.scope_matcher'] = ScopeMatcher::class;
        $services['security.helper'] = Security::class;
        $services['translator'] = TranslatorInterface::class;

        return $services;
    }

    /**
     * @param Template $template
     * @param ModuleModel $model
     * @param Request $request
     * @return null|Response
     * @throws \Exception
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        /** @var StringUtil $stringUtilAdapter */
        $stringUtilAdapter = $this->get('contao.framework')->getAdapter(StringUtil::class);

        /** @var Database $databaseAdapter */
        $databaseAdapter = $this->get('contao.framework')->getAdapter(Database::class);

        // Download link
        $template->downloadUrl = $this->page->getFrontendUrl() . '?act=downloadJahresprogrammXls';

        // Die ganze Tabelle
        $objJumpTo = PageModel::findByPk($model->rszJahresprogrammReaderPage);
        $arrJahresprogramm = [];
        $objJahresprogramm = $databaseAdapter->getInstance()
            ->execute("SELECT * FROM tl_rsz_jahresprogramm ORDER BY start_date ASC");

        while ($objJahresprogramm->next())
        {
            $arrJahresprogramm[] = [
                'id'              => $objJahresprogramm->id,
                'kw'              => $objJahresprogramm->kw,
                'start_date'      => Date::parse('Y-m-d', (int) $objJahresprogramm->start_date),
                'end_date'        => Date::parse('Y-m-d', (int) $objJahresprogramm->end_date),
                'tstamp_end_date' => $objJahresprogramm->end_date,
                'art'             => $objJahresprogramm->art,
                'kommentar'       => $objJahresprogramm->kommentar,
                'ort'             => $stringUtilAdapter->substr($objJahresprogramm->ort, 40),
                'trainer'         => $objJahresprogramm->trainer,
                'autoSignIn'      => $objJahresprogramm->autoSignIn,
                'signInStop'      => $objJahresprogramm->autoSignIn ? Date::parse('Y-m-d', $objJahresprogramm->registrationStop) : '',
                'jumpTo'          => $objJumpTo ? $objJumpTo->getFrontendUrl('/' . $objJahresprogramm->id) : '',
            ];
        }
        $template->Jahresprogramm = $arrJahresprogramm;

        // Die nächsten Events
        $arrNextEvent = [];
        $objJahresprogramm = $databaseAdapter->getInstance()
            ->prepare("SELECT * FROM tl_rsz_jahresprogramm WHERE start_date > ? ORDER BY start_date, id")
            ->limit(4)
            ->execute(time());

        while ($objJahresprogramm->next())
        {
            $arrNextEvent[] = [
                'id'              => $objJahresprogramm->id,
                'kw'              => $objJahresprogramm->kw,
                'start_date'      => Date::parse('Y-m-d', (int) $objJahresprogramm->start_date),
                'end_date'        => Date::parse('Y-m-d', (int) $objJahresprogramm->end_date),
                'tstamp_end_date' => $objJahresprogramm->end_date,
                'art'             => $objJahresprogramm->art,
                'kommentar'       => $objJahresprogramm->kommentar,
                'ort'             => $stringUtilAdapter->substr($objJahresprogramm->ort, 40),
                'trainer'         => $stringUtilAdapter->substr($objJahresprogramm->trainer, 10),
                'autoSignIn'      => $objJahresprogramm->autoSignIn,
                'signInStop'      => $objJahresprogramm->autoSignIn ? Date::parse('Y-m-d', $objJahresprogramm->registrationStop) : '',
                'jumpTo'          => $objJumpTo ? $objJumpTo->getFrontendUrl('/' . $objJahresprogramm->id) : '',
            ];
        }
        $template->arrNextEvent = $arrNextEvent;

        return $template->getResponse();
    }

    /**
     * @throws \Exception
     */
    public function downloadJahresprogrammXls()
    {
        /** @var Database $databaseAdapter */
        $databaseAdapter = $this->get('contao.framework')->getAdapter(Database::class);

        $arrFields = $databaseAdapter->getInstance()->getFieldNames('tl_rsz_jahresprogramm');
        // Exclude dome fields
        $arrExclude = ['id', 'tstamp', 'kw', 'uniqueId'];
        $arrFields = array_diff($arrFields, $arrExclude);
        $options = [
            'strSorting'         => 'start_date ASC',
            'destinationCharset' => 'Windows-1252',
            'arrSelectedFields'  => $arrFields,
        ];

        ExportTable::exportTable('tl_rsz_jahresprogramm', $options);
    }

}

