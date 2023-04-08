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

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

#[AsFrontendModule(RszJahresprogrammReaderModuleController::TYPE, category: 'rsz_frontend_modules', template: 'mod_rsz_jahresprogramm_reader')]
class RszJahresprogrammReaderModuleController extends AbstractFrontendModuleController
{
    public const TYPE = 'rsz_jahresprogramm_reader_module';

    private ?FrontendUser $objUser = null;
    private ?RszJahresprogrammModel $objEvent = null;

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly Security $security,
        private readonly ScopeMatcher $scopeMatcher,
    ) {
    }

    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        $inputAdapter = $this->framework->getAdapter(Input::class);
        $configAdapter = $this->framework->getAdapter(Config::class);
        $environmentAdapter = $this->framework->getAdapter(Environment::class);

        // Get logged in frontend user
        $user = $this->security->getUser();

        if ($user instanceof FrontendUser) {
            $this->objUser = $user;
        }

        if ($this->scopeMatcher->isFrontendRequest($request)) {
            $blnShow = false;

            $page->noSearch = 1;
            $page->cache = 0;

            // Set the item from the auto_item parameter
            if (!isset($_GET['events']) && $configAdapter->get('useAutoItem') && isset($_GET['auto_item'])) {
                $inputAdapter->setGet('events', $inputAdapter->get('auto_item'));
            }

            if ('' !== $inputAdapter->get('events')) {
                $this->objEvent = RszJahresprogrammModel::findByPk($inputAdapter->get('events'));

                if (null !== $this->objEvent) {
                    $blnShow = true;
                    $page->noSearch = 0;
                    $page->cache = 1;
                }
            }

            if (!$blnShow) {
                throw new PageNotFoundException('Page not found: '.$environmentAdapter->get('uri'));
            }
        }

        return parent::__invoke($request, $model, $section, $classes);
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $inputAdapter = $this->framework->getAdapter(Input::class);
        $controllerAdapter = $this->framework->getAdapter(Controller::class);
        $stringUtilAdapter = $this->framework->getAdapter(StringUtil::class);
        $databaseAdapter = $this->framework->getAdapter(Database::class);

        $userIsNotAllowedToSignIn = false;

        if (null !== $this->objEvent) {
            if ($this->objUser && $this->objEvent->autoSignIn) {
                $template->User = MemberModel::findByPk($this->objUser);
                $objBackendUser = UserModel::findByUsername($this->objUser->username);

                // Handle form input for event subscription
                if (null !== $this->objUser && 'tl_rsz_jahresprogramm_participant' === $inputAdapter->post('FORM_SUBMIT')) {
                    $objDb = $databaseAdapter->getInstance()
                        ->prepare('SELECT * FROM tl_rsz_jahresprogramm_participant WHERE pid=? AND uniquePid=?')
                        ->execute($this->objUser->id, $this->objEvent->uniqueId)
                    ;

                    if ($objDb->numRows) {
                        $objParticipant = RszJahresprogrammParticipantModel::findByPk($objDb->id);
                    } else {
                        $objParticipant = new RszJahresprogrammParticipantModel();
                        $objParticipant->addedOn = time();
                    }
                    $objParticipant->uniquePid = $this->objEvent->uniqueId;
                    $objParticipant->pid = $this->objUser->id;
                    $objParticipant->tstamp = time();
                    $objParticipant->signOffReason = $inputAdapter->post('signOffReason');
                    $objParticipant->signedIn = '';
                    $objParticipant->signedOff = '';

                    if ('true' === $inputAdapter->post('signIn')) {
                        $objParticipant->signedIn = '1';
                    } else {
                        $objParticipant->signedOff = '1';
                    }
                    $objParticipant->tstamp = time();
                    $objParticipant->save();
                    $controllerAdapter->reload();
                }

                // Auto sign In
                $blnUserIsAutoSignedIn = false;
                $arrFunktion = $stringUtilAdapter->deserialize($objBackendUser->funktion, true);

                if (\in_array('Athlet', $arrFunktion, true)) {
                    if ($this->objEvent->autoSignIn) {
                        $arrKategories = $stringUtilAdapter->deserialize($this->objEvent->autoSignInKategories, true);

                        if (\in_array($objBackendUser->kategorie, $arrKategories, true)) {
                            $blnUserIsAutoSignedIn = true;
                        }
                    }
                }

                $objParticipant = $databaseAdapter->getInstance()
                    ->prepare('SELECT * FROM tl_rsz_jahresprogramm_participant WHERE pid=? AND uniquePid=?')
                    ->execute($this->objUser->id, $this->objEvent->uniqueId)
                ;

                if ($objParticipant->numRows) {
                    $arrParticipant = $objParticipant->row();
                    $template->formData = $arrParticipant;

                    if ('1' === $arrParticipant['signedIn']) {
                        $template->signInText = 'Super, du hast dich für diesen Anlass angemeldet.'.('' !== $objParticipant->signOffReason ? '{{br}}{{br}}<strong>Mitteilung:</strong>{{br}}'.$objParticipant->signOffReason : '');
                        $template->alertClass = 'success';
                        $template->formButtonText = 'Abmelden';
                    } elseif ('1' === $arrParticipant['signedOff']) {
                        $template->signInText = 'Du hast dich für diesen Anlass abgemeldet.'.('' !== $objParticipant->signOffReason ? '{{br}}{{br}}<strong>Grund:</strong>{{br}}'.$objParticipant->signOffReason : '');
                        $template->alertClass = 'danger';
                        $template->formButtonText = 'Anmelden';
                    }
                } elseif ($blnUserIsAutoSignedIn) {
                    $template->signInText = 'Du bist für diesen Anlass automatisch angemeldet.';
                    $template->alertClass = 'success';
                    $template->formButtonText = 'Abmelden';
                } elseif (!$this->objEvent->autoSignIn) {
                    $template->formData = null;
                    $template->signInText = 'Du hast dich für diesen Anlass noch nicht angemeldet.';
                    $template->alertClass = 'info';
                    $template->formButtonText = 'Anmelden';
                } else {
                    $userIsNotAllowedToSignIn = true;
                }
            }
        }

        $arrJahresprogramm = [
            'id' => $this->objEvent->id,
            'kw' => $this->objEvent->kw,
            'start_date' => Date::parse('Y-m-d', (int) $this->objEvent->start_date),
            'end_date' => Date::parse('Y-m-d', (int) $this->objEvent->end_date),
            'art' => $this->objEvent->art,
            'teilnehmer' => implode(', ', StringUtil::deserialize($this->objEvent->teilnehmer, true)),
            'kommentar' => $this->objEvent->kommentar,
            'ort' => $this->objEvent->ort,
            'trainer' => $this->objEvent->trainer,
            'autoSignIn' => $this->objEvent->autoSignIn,
        ];

        $template->displayForm = $this->objUser && $this->objEvent->autoSignIn ? true : false;

        if ($userIsNotAllowedToSignIn) {
            $template->displayForm = false;
        }

        $template->blnSignInPerionHasExpired = $this->objEvent->registrationStop < time() ? true : false;
        $template->Jahresprogramm = $arrJahresprogramm;

        return $template->getResponse();
    }
}
