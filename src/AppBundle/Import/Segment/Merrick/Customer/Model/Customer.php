<?php

namespace AppBundle\Import\Segment\Merrick\Customer\Model;

use As3\SymfonyData\Import\Segment;

class Customer extends Segment
{
    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'merrick_customer_model_customer';
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->source->count('users_v2', $this->getCriteria());
    }

    /**
     * {@inheritdoc}
     */
    public function modify($limit = 200, $skip = 0)
    {
        $kvs = [];
        $docs = $this->source->retrieve('users_v2', $this->getCriteria(), $this->getFields(), $this->getSort(), $limit, $skip);
        $now = new \DateTime();

        foreach ($docs as $doc) {
            var_dump($doc);
            die(__METHOD__);

            $kv = [
                'name'      => $this->formatText($doc['title']),
                'body'      => null,
                'redirects' => [ $this->formatRedirect($doc['permalink']) ],
                'legacy'    => [
                    'id'        => $doc['objid'],
                    'source'    => $this->getKey(),
                    'cost'      => 'Free',
                ],
                'published' => new \DateTime($doc['releasedate']),
                'expires'   => new \DateTime($doc['expirationdate']),
                'created'   => new \DateTime($doc['creationdate']),
                'updated'   => new \DateTime($doc['lastmodified']),
                'status'    => 'Published',
                'touched'   => $now
            ];
            if (isset($doc['summary'])) {
                $kv['legacy']['summary'] = $this->formatText($doc['summary']);
            }
            $kv['body'] = $this->formatBody($doc['body'] ?: $doc['summary'], $kv);
            $kvs[] = $kv;
        }
        return $kvs;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(array $items)
    {
        return $this->importer->getPersister()->batchInsert('customer', $items);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCriteria()
    {
        return ['site' => 'www.vehicleservicepros.com'];
    }
}
