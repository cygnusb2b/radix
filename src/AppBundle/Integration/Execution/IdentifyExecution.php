<?php

namespace AppBundle\Integration\Execution;

use AppBundle\Integration\Definition\AbstractDefinition;
use AppBundle\Integration\Definition\ExternalIdentityDefinition;
use AppBundle\Integration\Handler\HandlerInterface;
use AppBundle\Integration\Handler\IdentifyInterface;
use As3\Modlr\Models\Model;

class IdentifyExecution extends AbstractExecution
{
    /**
     * Executes the identify integration.
     * Any logic contained in this method will be run for ALL integration services!
     *
     * @param   string  $externalId
     * @return  Model
     */
    public function run($externalId)
    {
        $handler = $this->getHandler();
        list($source, $identifier) = $handler->getSourceAndIdentifierFor($externalId);

        $source   = sprintf('identify:%s', $source);
        $identity = $this->getStore()->findQuery('identity-external', ['source' => $source, 'identifier' => $identifier])->getSingleResult();
        if (null === $identity) {
            // Immediately create. Will update the model data later.
            $identity = $this->getStore()->create('identity-external');
            $identity->set('source', $source);
            $identity->set('identifier', $identifier);
            // $identity->save(); // @todo uncomment once the application is done post-process.
        }

        // @todo At this point, the actual identification and updating of the identity model should be handled post-process.

        // Get all question-pull integrations that match this service.
        $definition = $handler->execute($identifier, $this->extractExternalQuestionIds());
        $this->applyIdentityValues($identity, $definition);

        $identity->save();

        // Handle question answers.
        $this->upsertAnswers($identity, $definition);
        return $identity;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedModelType()
    {
        return 'integration-identify';
    }

    /**
     * {@inheritdoc}
     */
    protected function validateImplements(HandlerInterface $handler)
    {
        if (!$handler instanceof IdentifyInterface) {
            throw new \InvalidArgumentException('The handler is unsupported. Expected an implementation of IdentifyInterface');
        }
    }

    /**
     * Applies the identity values from a definition to a model.
     *
     * @param   Model                       $identity
     * @param   ExternalIdentityDefinition  $definition
     */
    private function applyIdentityValues(Model $identity, ExternalIdentityDefinition $definition)
    {
        $attributes  = $definition->getAttributes();

        foreach (['givenName', 'familyName', 'middleName', 'salutation', 'suffix', 'gender', 'title', 'companyName', 'externalId'] as $key) {
            $identity->set($key, $attributes->get($key));
        }

        $this->applyEmbedManyFor($identity, 'emails', ['identifier', 'description', 'isPrimary', 'value', 'emailType'], $definition->getEmails());
        $this->applyEmbedManyFor($identity, 'phones', ['identifier', 'description', 'isPrimary', 'number', 'phoneType'], $definition->getPhones());
        $this->applyEmbedManyFor($identity, 'addresses', ['identifier', 'description', 'isPrimary', 'companyName', 'street', 'extra', 'city', 'regionCode', 'countryCode', 'postalCode'], $definition->getAddresses());
    }

    /**
     * @param   Model                   $identity
     * @param   string                  $fieldKey
     * @param   array                   $fields
     * @param   AbstractDefinition[]    $definitions
     */
    private function applyEmbedManyFor(Model $identity, $fieldKey, array $fields, array $definitions)
    {
        $identity->clear($fieldKey);
        foreach ($definitions as $definition) {
            $embed      = $identity->createEmbedFor($fieldKey);
            $attributes = $definition->getAttributes();
            foreach ($fields as $key) {
                $embed->set($key, $attributes->get($key));
            }
            $identity->pushEmbed($fieldKey, $embed);
        }
    }

    /**
     * Gets all external question identifiers related to this identify request.
     *
     * @return  array
     */
    private function extractExternalQuestionIds()
    {
        $identifiers = [];
        $criteria    = [
            'type'       => ['$in' => ['integration-question-pull', 'integration-question-push']],
            'service'    => $this->getIntegration()->get('service')->getId(),
            'boundTo'    => 'identity',
            'identifier' => ['$exists' => true]
        ];

        $collection = $this->getStore()->findQuery('integration', $criteria);
        foreach ($collection as $integration) {
            if (false === $integration->get('enabled')) {
                continue;
            }
            $identifiers[$integration->get('identifier')]  = true;
        }
        return array_keys($identifiers);
    }

    private function upsertAnswers(Model $identity, ExternalIdentityDefinition $definition)
    {
        var_dump(__METHOD__, $definition->getAnswers());
        die();
    }
}
