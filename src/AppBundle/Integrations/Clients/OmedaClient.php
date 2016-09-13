<?php

namespace AppBundle\Integrations\Clients;

use AppBundle\Integrations\Definitions\QuestionChoiceDefinition;
use AppBundle\Integrations\Definitions\QuestionDefinition;
use As3\OmedaSDK\ApiClient;
use As3\Parameters\DefinedParameters as Parameters;
use As3\Parameters\Definitions;

class OmedaClient extends AbstractClient
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var array
     */
    private $brandData = [];

    /**
     * @var bool
     */
    private $useStaging = false;

    /**
     * Constructor.
     *
     * @param   ApiClient  $apiClient
     * @param   array|null $parameters
     */
    public function __construct(ApiClient $apiClient, array $parameters = null)
    {
        $this->apiClient = $apiClient;
        parent::__construct($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function configure(array $parameters)
    {
        parent::configure($parameters);
        $this->apiClient->configure($this->parameters->toArray());
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function executeQuestionPull($identifier, array $args = [])
    {
        $this->validateCanExecute();
        $demographic = $this->extractDemographicDataFor($identifier);
        return $this->createQuestionDefinition($demographic);
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'omeda';
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterDefinitions()
    {
        $definitions = new Definitions();
        return $definitions
            ->add('clientKey', 'string', null, true)
            ->add('brandKey', 'string', null, true)
            ->add('appId', 'string', null, true)
            ->add('inputId', 'string', null, true)
        ;
    }

    /**
     * Sets whether to use the Omeda staging API.
     *
     * @param   bool    $bit
     * @return  self
     */
    public function useStaging($bit = true)
    {
        $this->useStaging = (boolean) $bit;
        $this->apiClient->useStaging($bit);
        return $this;
    }

    /**
     * Creates a question definition instance from an Omeda demographic.
     *
     * @param   array   $data
     * @return  QuestionDefinition
     */
    private function createQuestionDefinition(array $data)
    {
        $definition = new QuestionDefinition(
            $data['Description'],
            $this->getQuestionTypeFor($data['DemographicType'])
        );

        if (isset($data['DemographicValues']) && is_array($data['DemographicValues'])) {
            foreach ($data['DemographicValues'] as $value) {
                $choice = $this->createQuestionChoiceDefinition($value);
                $definition->addChoiceDefinition($choice);
            }
        }
        return $definition;
    }

    /**
     * Creates a question choice definition instance from an Omeda demographic value.
     *
     * @param   array   $value
     * @return  QuestionChoiceDefinition
     */
    private function createQuestionChoiceDefinition(array $value)
    {
        $definition = new QuestionChoiceDefinition(
            $value['ShortDescription'],
            $this->getChoiceTypeFor($value['DemographicValueType'])
        );
        $definition->setExternalId($value['Id']);

        if (isset($value['AlternateId'])) {
            $definition->setAlternateId($value['AlternateId']);
        }
        if (isset($value['Sequence'])) {
            $definition->setSequence($value['Sequence']);
        }
        return $definition;
    }

    /**
     * Extracts an Omeda demographic data from the API for the provided identifier.
     *
     * @param   string  $identifier
     * @return  array
     * @throws  \RuntimeException|\InvalidArgumentException
     */
    private function extractDemographicDataFor($identifier)
    {
        $brandData = $this->getBrandData();
        if (!isset($brandData['Demographics']) || !is_array($brandData['Demographics'])) {
            throw new \RuntimeException('No demographic information was found in the Omeda brand data.');
        }

        $found = null;
        foreach ($brandData['Demographics'] as $demographic) {
            if (isset($demographic['Id']) && $demographic['Id'] == $identifier) {
                $found = $demographic;
                break;
            }
        }
        if (empty($found)) {
            throw new \InvalidArgumentException(sprintf('No Omeda demographic found for ID "%s"', $identifier));
        }
        return $found;
    }

    /**
     * Gets the internal choice type for an Omeda demographic value type.
     *
     * @param   int     $omedaType
     * @return  string
     * @throws  \InvalidArgumentException
     */
    private function getChoiceTypeFor($omedaType)
    {
        $map = [
            0  => 'standard',
            3  => 'none',
            4  => 'other'
        ];
        if (!isset($map[$omedaType])) {
            throw new \InvalidArgumentException('No corresponding choice type was found for Omeda demographic value type "%s"', $omedaType);
        }
        return $map[$omedaType];
    }

    /**
     * Gets the internal quesiton type for an Omeda demographic type.
     *
     * @param   int     $omedaType
     * @return  string
     * @throws  \InvalidArgumentException
     */
    private function getQuestionTypeFor($omedaType)
    {
        $map = [
            1  => 'choice-single',
            2  => 'choice-multiple',
            3  => 'string',
            5  => 'boolean',
            6  => 'datetime',
            7  => 'integer',
            8  => 'float',
        ];
        if (!isset($map[$omedaType])) {
            throw new \InvalidArgumentException('No corresponding question type was found for Omeda demographic type "%s"', $omedaType);
        }
        return $map[$omedaType];
    }

    /**
     * Gets the brand data from Omeda, if not already loaded in memory.
     *
     * @return  array
     */
    private function getBrandData()
    {
        $client = $this->parameters->get('clientKey');
        $brand  = $this->parameters->get('brandKey');

        if (!isset($this->brandData[$client][$brand])) {
            $response = $this->apiClient->brand()->lookup();
            if (200 !== $response->getStatusCode()) {
                throw new \RuntimeException('The Omeda brand API request was not successful.');
            }
            $data = @json_decode($response->getBody()->getContents(), true);
            if (empty($data)) {
                throw new \RuntimeException('The Omeda brand API returned an empty response.');
            }
            $this->brandData[$client][$brand] = $data;
        }
        return $this->brandData[$client][$brand];
    }
}
