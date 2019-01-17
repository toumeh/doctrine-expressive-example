<?php

declare(strict_types=1);

namespace Announcements\Handler;

use Doctrine\ORM\EntityManager;
use Zend\Expressive\Helper\ServerUrlHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class AnnouncementsViewHandler
 * @package Announcements\Handler
 */
class AnnouncementsViewHandler implements RequestHandlerInterface
{
    protected $entityManager;
    protected $urlHelper;

    /**
     * AnnouncementsViewHandler constructor.
     * @param EntityManager $entityManager
     * @param ServerUrlHelper $urlHelper
     */
    public function __construct(
        EntityManager $entityManager,
        ServerUrlHelper $urlHelper
    ) {
        $this->entityManager = $entityManager;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $result = [];

        $entityRepository = $this->entityManager->getRepository('Announcements\Entity\Announcement');

        $return = $entityRepository->find($request->getAttribute('id'));

        if (empty($return)) {
            $result['_error']['error'] = 'not_found';
            $result['_error']['error_description'] = 'Record not found.';

            return new JsonResponse($result, 404);
        }

        // add hypermedia links
        $result['Result']['_links']['self'] = $this->urlHelper->generate('/announcements/'.$return->getId());
        $result['Result']['_links']['create'] = $this->urlHelper->generate('/announcements/');
        $result['Result']['_links']['read'] = $this->urlHelper->generate('/announcements/');
        $result['Result']['_links']['update'] = $this->urlHelper->generate('/announcements/'.$return->getId());
        $result['Result']['_links']['delete'] = $this->urlHelper->generate('/announcements/'.$return->getId());

        $result['Result']['_embedded']['Announcement'] = $return->getAnnouncement();

        return new JsonResponse($result);
    }
}
