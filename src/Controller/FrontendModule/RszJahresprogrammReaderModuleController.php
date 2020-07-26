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

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Database;
use Contao\Date;
use Contao\Environment;
use Contao\FrontendUser;
use Contao\Input;
use Contao\MemberModel;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\Template;
use Contao\UserModel;
use Markocupic\RszJahresprogrammBundle\Model\RszJahresprogrammModel;
use Markocupic\RszJahresprogrammBundle\Model\RszJahresprogrammParticipantModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RszJahresprogrammReaderModuleController
 * @package Markocupic\RszJahresprogrammBundle\Controller\FrontendModule
 */
class RszJahresprogrammReaderModuleController extends AbstractFrontendModuleController
{
    /** @var RequestStack */
    private $requestStack;

    /** @var ScopeMatcher */
    private $scopeMatcher;

    /** @var PageModel */
    protected $page;

    /** @var  FrontendUser */
    protected $objUser;

    /** @var RszJahresprogrammModel */
    private $objEvent;

    /**
     * RszJahresprogrammReaderModuleController constructor.
     * @param RequestStack $requestStack
     * @param ScopeMatcher $scopeMatcher
     */
    public function __construct(RequestStack $requestStack, ScopeMatcher $scopeMatcher)
    {
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
    }

    /**
     * This method extends the parent __invoke method,
     * its usage is usually not necessary
     * @param Request $request
     * @param ModuleModel $model
     * @param string $section
     * @param array|null $classes
     * @param PageModel|null $page
     * @return Response
     */
    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        /** @var Input $inputAdapter */
        $inputAdapter = $this->get('contao.framework')->getAdapter(Input::class);

        /** @var Config $configAdapter */
        $configAdapter = $this->get('contao.framework')->getAdapter(Config::class);

        /** @var Environment $environmentAdapter */
        $environmentAdapter = $this->get('contao.framework')->getAdapter(Environment::class);

        // Get the page model
        $this->page = $page;

        // Get logged in frontend user
        $user = $this->get('security.helper')->getUser();
        if ($user instanceof FrontendUser)
        {
            $this->objUser = $user;
        }

        if ($this->scopeMatcher->isFrontendRequest($this->requestStack->getCurrentRequest()))
        {
            $blnShow = false;

            $this->page->noSearch = 1;
            $this->page->cache = 0;

            // Set the item from the auto_item parameter
            if (!isset($_GET['events']) && $configAdapter->get('useAutoItem') && isset($_GET['auto_item']))
            {
                $inputAdapter->setGet('events', $inputAdapter->get('auto_item'));
            }

            if ($inputAdapter->get('events') != '')
            {
                $this->objEvent = RszJahresprogrammModel::findByPk($inputAdapter->get('events'));
                if ($this->objEvent !== null)
                {
                    $blnShow = true;
                    $this->page->noSearch = 0;
                    $this->page->cache = 1;
                }
            }

            if (!$blnShow)
            {
                throw new PageNotFoundException('Page not found: ' . $environmentAdapter->get('uri'));
            }
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
        //$services['database_connection'] = Connection::class;
        //$services['contao.routing.scope_matcher'] = ScopeMatcher::class;
        $services['security.helper'] = Security::class;
        $services['translator'] = TranslatorInterface::class;

        return $services;
    }

    /**
     * @param Template $template
     * @param ModuleModel $model
     * @param Request $request
     * @return null|Response
     */
    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        /** @var Input $inputAdapter */
        $inputAdapter = $this->get('contao.framework')->getAdapter(Input::class);

        /** @var Controller $controllerAdapter */
        $controllerAdapter = $this->get('contao.framework')->getAdapter(Controller::class);

        /** @var StringUtil $stringUtilAdapter */
        $stringUtilAdapter = $this->get('contao.framework')->getAdapter(StringUtil::class);

        $userIsNotAllowedToSignIn = false;

        if ($this->objEvent !== null)
        {
            if ($this->objUser && $this->objEvent->autoSignIn)
            {
                $template->User = MemberModel::findByPk($this->objUser);
                $objBackendUser = UserModel::findByUsername($this->objUser->username);

                // Handle Form Inputfor event subscription
                if ($this->objUser !== null && $inputAdapter->post('FORM_SUBMIT') == 'tl_rsz_jahresprogramm_participant')
                {
                    $objDb = Database::getInstance()
                        ->prepare('SELECT * FROM tl_rsz_jahresprogramm_participant WHERE pid=? AND uniquePid=?')
                        ->execute($this->objUser->id, $this->objEvent->uniqueId);
                    if ($objDb->numRows)
                    {
                        $objParticipant = RszJahresprogrammParticipantModel::findByPk($objDb->id);
                    }
                    else
                    {
                        $objParticipant = new RszJahresprogrammParticipantModel();
                        $objParticipant->addedOn = time();
                    }
                    $objParticipant->uniquePid = $this->objEvent->uniqueId;
                    $objParticipant->pid = $this->objUser->id;
                    $objParticipant->tstamp = time();
                    $objParticipant->signOffReason = $inputAdapter->post('signOffReason');
                    $objParticipant->signedIn = '';
                    $objParticipant->signedOff = '';

                    if ($inputAdapter->post('signIn') == 'true')
                    {
                        $objParticipant->signedIn = '1';
                    }
                    else
                    {
                        $objParticipant->signedOff = '1';
                    }
                    $objParticipant->tstamp = time();
                    $objParticipant->save();
                    $controllerAdapter->reload();
                }

                // Auto sign In
                $blnUserIsAutoSignedIn = false;
                $arrFunktion = $stringUtilAdapter->deserialize($objBackendUser->funktion, true);
                if (in_array('Athlet', $arrFunktion))
                {
                    if ($this->objEvent->autoSignIn)
                    {
                        $arrKategories = $stringUtilAdapter->deserialize($this->objEvent->autoSignInKategories, true);
                        if (in_array($objBackendUser->kategorie, $arrKategories))
                        {
                            $blnUserIsAutoSignedIn = true;
                        }
                    }
                }

                $objParticipant = Database::getInstance()
                    ->prepare('SELECT * FROM tl_rsz_jahresprogramm_participant WHERE pid=? AND uniquePid=?')
                    ->execute($this->objUser->id, $this->objEvent->uniqueId);
                if ($objParticipant->numRows)
                {
                    $arrParticipant = $objParticipant->row();
                    $template->formData = $arrParticipant;

                    if ($arrParticipant['signedIn'] == '1')
                    {
                        $template->signInText = 'Super, du hast dich für diesen Anlass angemeldet.' . (($objParticipant->signOffReason != '') ? '{{br}}{{br}}<strong>Mitteilung:</strong>{{br}}' . $objParticipant->signOffReason : '');
                        $template->alertClass = 'success';
                        $template->formButtonText = 'Abmelden';
                    }
                    elseif ($arrParticipant['signedOff'] == '1')
                    {
                        $template->signInText = 'Du hast dich für diesen Anlass abgemeldet.' . (($objParticipant->signOffReason != '') ? '{{br}}{{br}}<strong>Grund:</strong>{{br}}' . $objParticipant->signOffReason : '');
                        $template->alertClass = 'danger';
                        $template->formButtonText = 'Anmelden';
                    }
                }
                elseif ($blnUserIsAutoSignedIn)
                {
                    $template->signInText = 'Du bist für diesen Anlass automatisch angemeldet.';
                    $template->alertClass = 'success';
                    $template->formButtonText = 'Abmelden';
                }
                elseif (!$this->objEvent->autoSignIn)
                {
                    $template->formData = null;
                    $template->signInText = 'Du hast dich für diesen Anlass noch nicht angemeldet.';
                    $template->alertClass = 'info';
                    $template->formButtonText = 'Anmelden';
                }
                else
                {
                    $userIsNotAllowedToSignIn = true;
                }
            }
        }

        $arrJahresprogramm = [
            'id'         => $this->objEvent->id,
            'kw'         => $this->objEvent->kw,
            'start_date' => Date::parse('Y-m-d', (int) $this->objEvent->start_date),
            'end_date'   => Date::parse('Y-m-d', (int) $this->objEvent->end_date),
            'art'        => $this->objEvent->art,
            'teilnehmer' => implode(', ', StringUtil::trimsplit(',', $this->objEvent->teilnehmer)),
            'kommentar'  => $this->objEvent->kommentar,
            'ort'        => $this->objEvent->ort,
            'trainer'    => $this->objEvent->trainer,
            'autoSignIn' => $this->objEvent->autoSignIn,
        ];

        $template->displayForm = ($this->objUser && $this->objEvent->autoSignIn) ? true : false;
        if ($userIsNotAllowedToSignIn)
        {
            $template->displayForm = false;
        }

        $template->blnSignInPerionHasExpired = $this->objEvent->registrationStop < time() ? true : false;
        $template->Jahresprogramm = $arrJahresprogramm;

        return $template->getResponse();
    }

}

