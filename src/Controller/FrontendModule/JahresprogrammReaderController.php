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

use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Database;
use Contao\Date;
use Contao\Environment;
use Contao\FrontendUser;
use Contao\Input;
use Contao\MemberModel;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\UserModel;
use Markocupic\RszJahresprogrammBundle\Model\RszJahresprogrammModel;
use Markocupic\RszJahresprogrammBundle\Model\RszJahresprogrammParticipantModel;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule(JahresprogrammReaderController::TYPE, category: 'rsz_frontend_modules')]
class JahresprogrammReaderController extends AbstractFrontendModuleController
{
    public const TYPE = 'jahresprogramm_reader';

    private MemberModel|null $user = null;
    private RszJahresprogrammModel|null $event = null;

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly Security $security,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly ContaoCsrfTokenManager $csrfTokenManager,
    ) {
    }

    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null, PageModel $page = null): Response
    {
        $inputAdapter = $this->framework->getAdapter(Input::class);
        $environmentAdapter = $this->framework->getAdapter(Environment::class);

        // Get logged in frontend user
        $user = $this->security->getUser();

        if ($user instanceof FrontendUser) {
            $this->user = MemberModel::findByPk($user->id);
        }

        if ($this->scopeMatcher->isFrontendRequest($request)) {
            $blnShow = false;

            $page->noSearch = 1;
            $page->cache = 0;



                $this->event = RszJahresprogrammModel::findByPk($inputAdapter->get('auto_item'));

                if (null !== $this->event) {
                    $blnShow = true;
                    $page->noSearch = 0;
                    $page->cache = 1;
                }


            if (!$blnShow) {
                throw new PageNotFoundException('Page not found: '.$environmentAdapter->get('uri'));
            }
        }

        return parent::__invoke($request, $model, $section, $classes);
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $inputAdapter = $this->framework->getAdapter(Input::class);
        $controllerAdapter = $this->framework->getAdapter(Controller::class);
        $stringUtilAdapter = $this->framework->getAdapter(StringUtil::class);
        $databaseAdapter = $this->framework->getAdapter(Database::class);

        $userIsNotAllowedToSignIn = false;

        if (null !== $this->event) {
            if (null !== $this->user && $this->event->autoSignIn) {
                $template->set('user', $this->user->row());
                $objBackendUser = UserModel::findByUsername($this->user->username);

                // Handle form input for event subscription
                if ('tl_rsz_jahresprogramm_participant' === $inputAdapter->post('FORM_SUBMIT')) {
                    $objDb = $databaseAdapter->getInstance()
                        ->prepare('SELECT * FROM tl_rsz_jahresprogramm_participant WHERE pid = ? AND uniquePid = ?')
                        ->execute($this->user->id, $this->event->uniqueId)
                    ;

                    if ($objDb->numRows) {
                        $objRegistration = RszJahresprogrammParticipantModel::findByPk($objDb->id);
                    } else {
                        $objRegistration = new RszJahresprogrammParticipantModel();
                        $objRegistration->addedOn = time();
                    }

                    $objRegistration->uniquePid = $this->event->uniqueId;
                    $objRegistration->pid = $this->user->id;
                    $objRegistration->tstamp = time();
                    $objRegistration->signOffReason = $inputAdapter->post('signOffReason');
                    $objRegistration->signedIn = '';
                    $objRegistration->signedOff = '';

                    if ('true' === $inputAdapter->post('signIn')) {
                        $objRegistration->signedIn = '1';
                    } else {
                        $objRegistration->signedOff = '1';
                    }

                    $objRegistration->tstamp = time();
                    $objRegistration->save();
                    $controllerAdapter->reload();
                }

                // Auto sign In
                $blnUserIsAutoSignedIn = false;
                $arrFunktion = $stringUtilAdapter->deserialize($objBackendUser->funktion, true);

                if (\in_array('Athlet', $arrFunktion, true)) {
                    if ($this->event->autoSignIn) {
                        $arrKategories = $stringUtilAdapter->deserialize($this->event->autoSignInKategories, true);

                        if (\in_array($objBackendUser->kategorie, $arrKategories, true)) {
                            $blnUserIsAutoSignedIn = true;
                            $template->set('request_uri', Environment::get('requestUri'));
                            $template->set('request_token', $this->csrfTokenManager->getDefaultTokenValue());
                        }
                    }
                }

                $objRegistration = $databaseAdapter->getInstance()
                    ->prepare('SELECT * FROM tl_rsz_jahresprogramm_participant WHERE pid=? AND uniquePid=?')
                    ->execute($this->user->id, $this->event->uniqueId)
                ;

                if ($objRegistration->numRows) {
                    $registration = $objRegistration->row();
                    $template->set('form_data', $registration);

                    if ('1' === $registration['signedIn']) {
                        $template->set('sign_in_text', 'Super, du hast dich für diesen Anlass angemeldet.'.('' !== $objRegistration->signOffReason ? '<br><br><strong>Mitteilung:</strong><br>'.$objRegistration->signOffReason : ''));
                        $template->set('alert_class', 'success');
                        $template->set('form_button_text', 'Abmelden');
                    } elseif ('1' === $registration['signedOff']) {
                        $template->set('sign_in_text', 'Du hast dich für diesen Anlass abgemeldet.'.('' !== $objRegistration->signOffReason ? '<br><br><strong>Grund:</strong><br>'.$objRegistration->signOffReason : ''));
                        $template->set('alert_class', 'danger');
                        $template->set('form_button_text', 'Anmelden');
                    }
                } elseif ($blnUserIsAutoSignedIn) {
                    $template->set('sign_in_text', 'Du bist für diesen Anlass automatisch angemeldet.');
                    $template->set('alert_class', 'success');
                    $template->set('form_button_text', 'Abmelden');
                } elseif (!$this->event->autoSignIn) {
                    $template->set('form_data', null);
                    $template->set('sign_in_text', 'Du hast dich für diesen Anlass noch nicht angemeldet.');
                    $template->set('alert_class', 'info');
                    $template->set('form_button_text', 'Anmelden');
                } else {
                    $userIsNotAllowedToSignIn = true;
                }
            }
        }

        $arrJahresprogramm = [
            'id' => $this->event->id,
            'kw' => $this->event->kw,
            'start_date' => Date::parse('Y-m-d', (int) $this->event->start_date),
            'end_date' => Date::parse('Y-m-d', (int) $this->event->end_date),
            'art' => $this->event->art,
            'teilnehmer' => implode(', ', StringUtil::deserialize($this->event->teilnehmer, true)),
            'kommentar' => $this->event->kommentar,
            'ort' => $this->event->ort,
            'trainer' => $this->event->trainer,
            'autoSignIn' => $this->event->autoSignIn,
        ];

        $template->set('display_form', $this->user && $this->event->autoSignIn ? true : false);

        if ($userIsNotAllowedToSignIn) {
            $template->set('displayForm', false);
        }

        $template->set('bln_sign_in_period_expired', $this->event->registrationStop < time() ? true : false);
        $template->set('event', $arrJahresprogramm);

        return $template->getResponse();
    }
}
