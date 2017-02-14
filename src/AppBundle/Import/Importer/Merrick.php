<?php

namespace AppBundle\Import\Importer;

use AppBundle\Core\AccountManager;
use As3\SymfonyData\Import\Importer;
use As3\SymfonyData\Import\ImporterInterface;
use As3\SymfonyData\Import\PersisterInterface;
use As3\SymfonyData\Import\SourceInterface;

abstract class Merrick extends Importer implements ImporterInterface
{
    /**
     * @var     AccountManager
     */
    protected $accountManager;

    /**
     * {@inheritdoc}
     */
    protected $supportedContexts = [
        'default'
    ];

    /**
     * @var     array
     * Hash containing legacy query criteria for applications
     */
    private $domains = [
        'acbm:ooh'      => 'www.oemoffhighway.com',
        'cygnus:vspc'   => 'www.vehicleservicepros.com',
        'anso'          => 'www.ansommag.com', // not a site anymore, use anyway?
        'autm'          => 'www.vendingmarketwatch',
        //'cato'          => 
        'cavc'          => 'www.aviationpros.com/', 
        'cgn'           => 'www.printingnews.com', 
        'cond'          => 'www.forconstructionpros.com',
        'csn'           => 'www.cpapracticeadvisor.com',
        //'cygc'          =>
        'emsr'          => 'www.emsworld.com',
        'fcp'           => 'www.forconstructionpros.com',
        'fg'            => 'www.feedandgrain.com',
        'fhc'           => 'www.firehouse.com',
        'fl'            => 'www.foodlogistics.com',
        'fms'           => 'www.vehicleservicepros.com',
        'frpc'          => 'www.forresidentialpros.com',
        'gip'           => 'www.greenindustrypros',
        'idex'          => 'www.idex.com', // not a site, what should we use here?
        'ido'           => 'www.ido.com', // not a site, what should we use here?
        'll'            => 'www.locksmithledger.com',
        'mass'          => 'www.masstransitmag.com',
        'ofcr'          => 'www.officer.com',
        'ooh'           => 'www.oemoffhighway.com',
        'pten'          => 'www.vehicleservicepros.com',
        'sdce'          => 'www.sdcexec.com',
        'siw'           => 'www.securityinfowatch.com',
        'siwi'          => 'www.securityinfowatch.com', // same site as siw?
        'vspc'          => 'www.vehicleservicepros.com',
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(AccountManager $accountManager, PersisterInterface $persister, SourceInterface $source)
    {
        $this->accountManager = $accountManager;
        return parent::__construct($persister, $source);
    }

    /**
     * Returns the legacy domain for the current application context
     *
     * @return  string
     * @throws  InvalidArgumentException
     */
    public function getDomain($key = null)
    {
        if (null === $key) {
            $key = $this->accountManager->getCompositeKey();
        }
        if (array_key_exists($key, $this->domains)) {
            return $this->domains[$key];
        }
        throw new \InvalidArgumentException(sprintf('Could not find legacy site value for account "%s!"', $key));
    }

    /**
     * Returns the group key for the current context 
     *
     * @return  string
     * @throws  InvalidArgumentException
     */
    public function getGroupKey()
    {
        return $this->accountManager->getApplication()->get('key');
    }
}
