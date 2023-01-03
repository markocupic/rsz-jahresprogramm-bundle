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

namespace Markocupic\RszJahresprogrammBundle\EventSubscriber;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AddFrontendAssetsSubscriber implements EventSubscriberInterface
{
    private ContaoFramework $framework;
    private RequestStack $requestStack;
    private ScopeMatcher $scopeMatcher;

    public function __construct(ContaoFramework $framework, RequestStack $requestStack, ScopeMatcher $scopeMatcher)
    {
        $this->framework = $framework;
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => 'addFrontendAssets'];
    }

    public function addFrontendAssets(RequestEvent $e): void
    {
        $this->framework->initialize();

        $request = $this->requestStack->getCurrentRequest();

        if ($request && $this->scopeMatcher->isBackendRequest($request)) {
            $GLOBALS['TL_CSS'][] = 'bundles/markocupicrszjahresprogramm/style_be.css';
        }
    }
}
