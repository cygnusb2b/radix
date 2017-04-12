<?php

namespace AppBundle\Import\Segment\Merrick\IdentityData;

use AppBundle\Import\Segment\Merrick\IdentityData;

class IdentityOptIn extends IdentityData
{
    /**
     * @var     array
     */
    private $integrations = [];

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->getCollectionForModel($this->getCollection())->count($this->getCriteria());
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_customer_identity_data_identity_optin';
    }

    /**
     * {@inheritdoc}
     */
    public function modify($limit = 200, $skip = 0)
    {
        $kvs = [];
        $docs = $this->getDocuments($limit, $skip);

        foreach ($docs as $doc) {
            foreach ($doc['legacy']['questions'] as $question) {
                $question['identity'] = ['id' => (string) $doc['_id'], 'type' => $doc['_type']];
                $question['email'] = $doc['legacy']['email'];
                $kv = $this->formatModel($question, $doc);
                if (null !== $kv) {
                    $kvs[] = $kv;
                }
            }
        }
        return $kvs;
    }

    /**
     * Returns formatted key-values for the passed legacy document
     *
     * @param   array   $doc    The legacy key values
     * @return  mixed   array of key values or null
     */
    protected function formatModel(array $doc)
    {
        // $question = $this->retrieveQuestion($doc['question']);
        $integration = $this->retrieveProductIntegration($doc['question']);

        if (null === $integration) {
            // var_dump(sprintf('Could not find answer using "%s" (question %s)', $doc['answer'], $doc['question']));
            return;
        }

        $productId = $integration['product'];
        return [
            'legacy'    => [
                'id'        => (string) $doc['identity']['id'],
                'source'    => sprintf('identity-omeda_%s', $doc['question'])
            ],
            'email'     => $doc['email'],
            'product'   => ['id' => $productId, 'type' => 'product-email-deployment'],
            'optedIn'   => (bool) $doc['answer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getCollection()
    {
        return 'identity';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDocuments($limit = 200, $skip = 0)
    {
        return $this->getCollectionForModel($this->getCollection())->find($this->getCriteria(), $this->getFields())->sort($this->getSort())->limit($limit)->skip($skip);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        return ['_type' => 'identity-account', 'legacy.questions' => ['$exists' => true]];
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelType()
    {
        return 'product-email-deployment-optin';
    }

    private function retrieveProductIntegration($legacyId)
    {
        if (!array_key_exists($legacyId, $this->integrations)) {
            $this->integrations[$legacyId] = $this->getCollectionForModel('integration-optin-push')->findOne(['_type' => 'integration-optin-push', 'identifier' => $legacyId]);
        }
        return $this->integrations[$legacyId];
    }

}
