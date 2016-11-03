<?php

namespace AppBundle\Integrations\Omeda;

use AppBundle\Integration\Handler\OptInPushInterface;

class OptInPushHandler extends AbstractHandler implements OptInPushInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($externalId, $emailAddress, $optedIn, array $extra = [])
    {
        var_dump(__METHOD__, $externalId, $emailAddress, $optedIn);
        die();
    }
}
