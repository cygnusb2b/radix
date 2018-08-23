<?php

namespace AppBundle\Integrations\Omeda;

use AppBundle\Integration\Definition\QuestionDefinition;
use AppBundle\Integration\Definition\QuestionChoiceDefinition;
use AppBundle\Integration\Handler\QuestionPullInterface;

class QuestionPullHandler extends AbstractHandler implements QuestionPullInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($externalId, array $extra = [])
    {
        $demographic = $this->extractDemographicDataFor($externalId);
        return $this->createQuestionDefinition($demographic);
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

        $attributes = $definition->getAttributes();
        $attributes->set('externalId', $value['Id']);

        if (isset($value['AlternateId'])) {
            $attributes->set('alternateId', $value['AlternateId']);
        }
        if (isset($value['Sequence'])) {
            $attributes->set('sequence', $value['Sequence']);
        }
        return $definition;
    }

    /**
     * Creates a question definition instance from an Omeda demographic.
     *
     * @param   array   $data
     * @return  QuestionDefinition
     */
    private function createQuestionDefinition(array $data)
    {
        $definition = new QuestionDefinition($data['Description'], $this->getQuestionTypeFor($data['DemographicType']));
        if (isset($data['DemographicValues']) && is_array($data['DemographicValues'])) {
            foreach ($data['DemographicValues'] as $value) {
                $choice = $this->createQuestionChoiceDefinition($value);
                $definition->addChoice($choice);
            }
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
        $demographic = $this->getDemographicData([$identifier => true]);
        if (empty($demographic)) {
            throw new \InvalidArgumentException(sprintf('No Omeda demographic found for ID "%s"', $identifier));
        }
        return reset($demographic);
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
}
