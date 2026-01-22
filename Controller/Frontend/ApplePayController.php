<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Controller\Frontend;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Override;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApplePayController extends AbstractController
{
    #[Route(
        path: '/.well-known/apple-developer-merchantid-domain-association',
        name: 'tpay_apple_pay_domain_verification'
    )]
    public function domainVerificationAction(): Response
    {
        $website = $this->container->get(WebsiteManager::class)->getCurrentWebsite();

        $content = $this->getContentFromConfig($website);

        if ($content === '') {
            /** @var LoggerInterface $logger */
            $logger = $this->container->get(LoggerInterface::class);

            $message = 'Apple Pay domain verification is not configured.';
            $logger->error($message, ['currentWebsite' => $website]);

            return new Response('Not configured', Response::HTTP_NOT_FOUND);
        }

        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }

    private function getContentFromConfig(?Website $website = null): string
    {
        return (string)$this->container->get(ConfigManager::class)
            ->get(
                'oro_tpay.apple_pay_domain_verification',
                false,
                false,
                $website
            );
    }

    #[Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                WebsiteManager::class,
                ConfigManager::class,
                LoggerInterface::class,
            ]
        );
    }
}
