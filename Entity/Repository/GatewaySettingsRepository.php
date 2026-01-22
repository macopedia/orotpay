<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Macopedia\Bundle\TpayBundle\Entity\GatewaySettings;

class GatewaySettingsRepository extends EntityRepository
{
    /**
     * @return GatewaySettings[]
     */
    public function getEnabledSettings(string $type): array
    {
        return $this->createQueryBuilder('settings')
            ->innerJoin('settings.channel', 'channel')
            ->andWhere('channel.enabled = true')
            ->andWhere('channel.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }
}
