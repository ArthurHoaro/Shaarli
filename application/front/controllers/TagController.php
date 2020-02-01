<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller;

use Slim\Http\Request;
use Slim\Http\Response;

class TagController extends ShaarliController
{
    /** @param string[] $args Route URL parameters (newTag) */
    public function addTag(Request $request, Response $response, array $args): Response
    {
        $newTag = trim($args['newTag'] ?? '');

        // Get previous URL (http_referer) and add the tag to the searchtags parameters in query.
        if (empty($this->container->environment['HTTP_REFERER'])) {
            // In case browser does not send HTTP_REFERER
            return $response->withRedirect('./?searchtags='. urlencode($newTag));
        }

        $parsedUrl = parse_url($this->container->environment['HTTP_REFERER']);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        // Prevent redirection loop (legacy URLs)
        unset($queryParams['addtag']);

        // We also remove page (keeping the same page has no sense, since the results are different)
        unset($queryParams['page']);

        if (empty($newTag)) {
            return $response->withRedirect($this->buildRedirectionUrl($parsedUrl, $queryParams));
        }

        // Check if this tag is already in the search query and ignore it if it is.
        $currentTags = isset($queryParams['searchtags']) ? explode(' ', $queryParams['searchtags']) : [];
        $currentTags = array_filter($currentTags, 'trim');
        $currentTags[] = $newTag;

        $queryParams['searchtags'] = implode(' ', array_unique($currentTags));


        return $response->withRedirect($this->buildRedirectionUrl($parsedUrl, $queryParams));
    }

    protected function buildRedirectionUrl(array $parsedUrl, array $queryParams): string
    {
        $path = !empty($parsedUrl['path']) ? $parsedUrl['path'] : '/';
        return $path . (!empty($queryParams) ? '?'. http_build_query($queryParams) : '');
    }
}
