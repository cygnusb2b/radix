<?php

namespace AppBundle\Controller\App;

use AppBundle\Serializer\PublicApiRules;
use AppBundle\Exception\HttpFriendlyException;
use AppBundle\Utility\HelperUtility;
use AppBundle\Utility\RequestUtility;
use As3\Modlr\Models\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PostController extends AbstractAppController
{
    public function listAction($type, $streamId, $page = 1)
    {
        $settings = $this->getPostSettings();
        $modelType = sprintf('post-%s', $type);
        $limit = $settings->get('pageSize');
        $skip = ($page - 1) * $limit;

        $payload = [
            'stream' => new \stdClass(),
            'posts' => [],
        ];

        $store = $this->get('as3_modlr.store');
        $stream = $store->findQuery('post-stream', ['identifier' => $streamId])->getSingleResult();
        if (null === $stream) {
            return new JsonResponse(['data' => $payload]);
        }
        // Determine whether the stream can still have new posts.
        $payload['stream']->active = $stream->get('active');

        $criteria = [
            'stream' => $stream->getId(), // Only posts for this stream
            'deleted' => ['$ne' => true], // Not deleted. Uses `$ne` in case `deleted` does not exist.
        ];
        if ('comment' === $type) {
            // Only show top-level comments, no children/replies.
            // @todo Comment replies/threads have not yet be implemented anywhere.
            $criteria['parent'] = ['$exists' => false];
        }

        $account = $this->get('app_bundle.identity.manager')->getActiveAccount();
        if ($account) {
            // Active account found. Include approved posts and any posts for the user.
            $criteria['$or'] = [
                ['approved' => ['$ne' => false]], // Approved. Uses `$ne` in case `approved` does not exist.
                ['account' => $account->getId()],
            ];
        } else {
            // No active account. Only include approved posts.
            $criteria['approved'] = ['$ne' => false]; // Approved. Uses `$ne` in case `approved` does not exist.
        }

        $serializer = $this->get('app_bundle.serializer.public_api');
        $serializer->addRule(new PublicApiRules\PostCommentSimpleRule());

        $posts = $store->findQuery($modelType, $criteria, [], ['createdDate' => -1], $skip, $limit);
        foreach ($posts as $post) {
            if ($post->get('anonymize')) {
                $post->set('displayName', 'Anonymous');
                $post->clear('picture');
            }
            $payload['posts'][] = $serializer->serialize($post)['data'];
        }
        return new JsonResponse(['data' => $payload]);
    }

    /**
     * Handles comment submissions.
     *
     * @param   Request $request
     * @return  JsonResponse
     */
    public function commentAction(Request $request)
    {
        $payload = RequestUtility::extractPayload($request);
        $this->validateSubmit($payload);

        // Retrieve the stream model (and create if it doesn't exists).
        $stream = $this->getOrCreateStream($payload['stream']);

        // Save the stream.
        $stream->save();

        // Create the new comment and save.
        $comment = $this->createPost($request, $stream, $payload, 'comment');
        $comment->save();

        $serializer = $this->get('app_bundle.serializer.public_api');
        $serializer->addRule(new PublicApiRules\PostCommentSimpleRule());
        $serialized = $serializer->serialize($comment);
        return new JsonResponse($serialized);
    }

    /**
     * Creates a new post model.
     *
     * @param Request $request
     * @param Model $stream
     * @param array $payload
     * @param string $type
     * @return Model
     */
    private function createPost(Request $request, Model $stream, array $payload, $type)
    {
        $post = $this->get('as3_modlr.store')->create(sprintf('post-%s', $type));
        $post->set('stream', $stream);
        $post->set('ipAddress', $request->getClientIp());
        $post->set('body', $payload['body']);

        $displayName = $payload['displayName'];
        $account = $this->get('app_bundle.identity.manager')->getActiveAccount();
        if ($account) {
            $post->set('picture', $account->get('picture'));
            $post->set('displayName', $displayName);

            $account->set('displayName', $displayName);
            $account->save();
        }

        $settings = $this->getPostSettings();
        $post->set('deleted', false);
        // Automatically approve if moderation is not required.
        $post->set('approved', !$settings->get('requireModeration'));
        // Anonymize post if it is allowed and it has been specified.
        $post->set('anonymize', $settings->get('allowAnonymous') && isset($payload['anonymize']) && $payload['anonymize']);
        // Set the account that posted. May be null depending on settings.
        $post->set('account', $this->get('app_bundle.identity.manager')->getActiveAccount());
        // @todo Need to add support for banned, included when a user is set to banned, all old posts needs to be updated.
        $isBanned = $account && $account->get('isBanned');
        $post->set('banned', $isBanned);

        return $post;
    }

    /**
     * Gets an existing stream, or creates it if new.
     *
     * @param array $payload
     * @return Model
     */
    private function getOrCreateStream(array $payload)
    {
        $store = $this->get('as3_modlr.store');
        $stream = $store->findQuery('post-stream', ['identifier' => $payload['identifier']])->getSingleResult();
        if (null === $stream) {
            $stream = $store->create('post-stream');
            $stream->set('identifier', $payload['identifier']);
        }
        $stream->set('url', $payload['url']);
        $stream->set('title', $payload['title']);
        return $stream;
    }

    /**
     * Retrieves the application settings for posts.
     * @return Model
     */
    private function getPostSettings()
    {
        return $this->getApplication()->get('settings')->get('posts');
    }

    /**
     * Validates the captcha payload.
     *
     * @param array $payload
     * @throws HttpFriendlyException
     */
    private function validateCaptcha(array $payload)
    {
        if (!isset($payload['captcha'])) {
            throw new HttpFriendlyException('No captcha data was provided with the request.', 400);
        }
        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_POST => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => [
                'secret'    => '6LcUfhAUAAAAAFvP-VMLGm5Y_e4M0vzMjJSL3Pcy',
                'response'  => $payload['captcha'],
            ],
        ]);
        $response = @json_decode(curl_exec($ch), true);
        if (!is_array($response) || !isset($response['success'])) {
            throw new HttpFriendlyException('Unable to extract the captcha response from the server.', 500);
        }
        if (!$response['success']) {
            $errors = isset($response['error-codes']) && is_array($response['error-codes']) ? implode(', ', $response['error-codes']) : '';
            throw new HttpFriendlyException(sprintf('The captcha response is invalid. %s', $errors), 400);
        }
    }

    /**
     * Validates the stream payload.
     *
     * @param array $payload
     * @throws HttpFriendlyException
     */
    private function validateStream(array $payload)
    {
        if (false === HelperUtility::isSetArray($payload, 'stream')) {
            throw new HttpFriendlyException('No stream data was provided for the post.', 400);
        }
        $required = ['identifier', 'url'];
        foreach ($required as $key) {
            if (false === HelperUtility::isSetNotEmpty($payload['stream'], $key)) {
                throw new HttpFriendlyException(sprintf('The required stream key `%s` was not found.', $key), 400);
            }
        }
    }

    /**
     * Validates that the post can be submitted.
     *
     * @param array $payload
     * @throws HttpFriendlyException
     */
    private function validateSubmit(array $payload)
    {
        if (empty($payload['displayName'])) {
            throw new HttpFriendlyException('Your display name (posting as) is required.', 400);
        }
        if (empty($payload['body'])) {
            throw new HttpFriendlyException('Your post cannot be empty.', 400);
        }
        $loggedIn = $this->get('app_bundle.identity.manager')->isAccountLoggedIn();
        $settings = $this->getPostSettings();

        if (!$settings->get('enabled')) {
            throw new HttpFriendlyException('Posts are not enabled for this application.', 400);
        }
        if ($settings->get('requireAccount') && !$loggedIn) {
            throw new HttpFriendlyException('You must be logged in to post.', 403);
        }
        $this->validateStream($payload);
        if ($settings->get('requireCaptcha') && !$settings->get('requireAccount')) {
            // Validate the captcha when it's required, and when users can post without an account.
            $this->validateCaptcha($payload);
        }
    }
}
