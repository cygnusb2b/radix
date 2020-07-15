<?php

namespace AppBundle\Factory;

use AppBundle\DataFormatter\MongoDBFormatter;
use AppBundle\Utility\HelperUtility;
use AppBundle\Utility\IpAddressUtility;
use AppBundle\Utility\RequestPayload;
use As3\Modlr\Models\AbstractModel;
use As3\Modlr\Models\Model;
use As3\Modlr\Store\Store;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Factory for submission models.
 *
 * @author  Jacob Bare <jacob.bare@gmail.com>
 */
class InputSubmissionFactory extends AbstractModelFactory implements SubscriberFactoryInterface
{
    /**
     * @var InputAnswerFactory
     */
    private $answer;

    /**
     * @var MongoDBFormatter
     */
    private $formatter;

    /**
     * @var RequestStack
     */
    private $requestStack;


    public function __construct(Store $store, InputAnswerFactory $answer, RequestStack $requestStack, MongoDBFormatter $formatter)
    {
        parent::__construct($store);
        $this->answer       = $answer;
        $this->requestStack = $requestStack;
        $this->formatter    = $formatter;
    }

    /**
     * Applies a request payload to the provided submission.
     *
     * @param   Model           $submission
     * @param   RequestPayload  $payload
     */
    public function apply(Model $submission, RequestPayload $payload)
    {
        $submission->set('payload', $payload->toArray());

        $metadata = $submission->getMetadata();
        foreach ($payload->getSubmission() as $key => $value) {
            if (true === $metadata->hasAttribute($key)) {
                $submission->set($key, $value);
            }
        }
        $this->setAnswers($submission, $payload);
    }

    /**
     * {@inheritdoc}
     */
    public function canSave(AbstractModel $submission)
    {
        $this->preValidate($submission);
        if (false === $submission->getState()->is('new')) {
            return new Error('Submissions are considered immutable. As such, you cannot modify pre-existing submissions.');
        }

        $sourceKey = $submission->get('sourceKey');
        if (empty($sourceKey)) {
            return new Error('The source key is required on all input submissions.');
        }

        foreach ($this->getRelatedAnswers($submission) as $answer) {
            if (true !== $result = $this->getAnswerFactory()->canSave($answer)) {
                // Ensure all answers can be saved.
                return $result;
            }
        }

        return true;
    }

    /**
     * Creates a new submission and applies the request payload.
     *
     * @param   RequestPayload  $payload
     * @return  Model
     */
    public function create(RequestPayload $payload)
    {
        $submission = $this->getStore()->create('input-submission');
        $this->apply($submission, $payload);
        return $submission;
    }

    /**
     * @return  InputAnswerFactory
     */
    public function getAnswerFactory()
    {
        return $this->answer;
    }

    /**
     * Gets all related models for the provided submission (including itself).
     *
     * @param   Model   $submission
     * @return  Model[]
     */
    public function getRelatedModelsFor(Model $submission)
    {
        return array_merge([$submission], $this->getRelatedAnswers($submission));
    }

    /**
     * {@inheritdoc}
     */
    public function preValidate(AbstractModel $submission)
    {
        $sourceKey = $submission->get('sourceKey');
        $sourceKey = trim($sourceKey);
        $sourceKey = empty($sourceKey) ? null : $sourceKey;
        $submission->set('sourceKey', $sourceKey);
    }

    /**
     * {@inheritdoc}
     */
    public function postSave(Model $model)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postValidate(AbstractModel $submission)
    {
        // Ensure raw payload data is formatted (for date time / numbers / etc).
        $formatted = $this->formatter->formatRaw((array) $submission->get('payload'));
        $submission->set('payload', $formatted);

        if (null !== $request = $this->requestStack->getCurrentRequest()) {
            // Append request specific items.
            $this->appendIpAddress($submission, $request);
            $this->appendRequestDetails($submission, $request);
        }

        // Remove any sensitive identity data.
        $payload = (array) $submission->get('payload');
        if (isset($payload['identity']['password'])) {
            unset($payload['identity']['password']);
            $submission->set('payload', $payload);
        }
        if (isset($payload['identity']['confirmPassword'])) {
            unset($payload['identity']['confirmPassword']);
            $submission->set('payload', $payload);
        }
    }

    /**
     * Saves the provided submission and all its related models, if valid.
     *
     * @param   Model   $submission
     */
    public function save(Model $submission)
    {
        if (true !== $result = $this->canSave($submission)) {
            $result->throwException();
        }
        foreach ($this->getRelatedModelsFor($submission) as $model) {
            $model->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Model $model)
    {
        return 'input-submission' === $model->getType();
    }

    /**
     * @param   Model   $model
     * @param   Request $request
     */
    private function appendIpAddress(Model $model, Request $request)
    {
        $ip = $request->getClientIp();
        if (IpAddressUtility::isIpVersion4($ip)) {
            $model->set('ipFour', $ip);
        }
        if (IpAddressUtility::isIpVersion6($ip)) {
            $model->set('ipSix', $ip);
        }
        $ipInfo = IpAddressUtility::geoCodeIp($ip);
        if (!empty($ipInfo)) {
            $model->set('ipInfo', $ipInfo);
        }
    }

    /**
     * @param   Model   $model
     * @param   Request $request
     */
    private function appendRequestDetails(Model $model, Request $request)
    {
        $embed = $model->createEmbedFor('request');
        $embed
            ->set('host', $request->getHost())
            ->set('method', $request->getMethod())
            ->set('path', $request->getPathInfo())
            ->set('query', $request->getQueryString())
            ->set('headers', (string) $request->headers)
        ;
        $model->set('request', $embed);
    }

    /**
     * This is needed in order to ensure newly created answers are also accounted for.
     * Modlr really needs to "automatically" append new inverse models to the owner's collection.
     *
     * @param   Model   $submission
     * @param   Model[]
     */
    private function getRelatedAnswers(Model $submission)
    {
        $answers = [];
        foreach ($this->getStore()->getModelCache()->getAll() as $type => $models) {
            if (0 !== stripos($type, 'input-answer-')) {
                continue;
            }
            foreach ($models as $answer) {
                if (null === $answer->get('submission')) {
                    continue;
                }
                if ($answer->get('submission')->getId() === $submission->getId()) {
                    $answers[$answer->getId()] = $answer;
                }
            }
        }
        foreach ($submission->get('answers') as $answer) {
            if (!isset($answers[$answer->getId()])) {
                $answers[$answer->getId()] = $answer;
            }
        }
        return $answers;
    }

    /**
     * Sets question answers to the submission model from both the identity and the submission.
     *
     * @param   Model           $submission
     * @param   RequestPayload  $payload
     */
    private function setAnswers(Model $submission, RequestPayload $payload)
    {
        // Save both identity and submission answers.
        $answers = array_merge(
            $payload->getIdentity()->get('answers', []),
            $payload->getSubmission()->get('answers', [])
        );

        $questionIds = [];
        foreach ($answers as $questionId => $answerId) {
            if (!HelperUtility::isMongoIdFormat($questionId)) {
                continue;
            }
            $questionIds[] = $questionId;
        }
        if (empty($questionIds)) {
            return;
        }
        $criteria  = ['id' => ['$in' => $questionIds]];
        $questions = $this->getStore()->findQuery('question', $criteria);

        foreach ($questions as $question) {
            $this->getAnswerFactory()->apply($submission, $question, $answers[$question->getId()]);
        }
    }
}
