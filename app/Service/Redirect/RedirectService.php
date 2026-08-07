<?php

declare(strict_types=1);

namespace App\Service\Redirect;

use App\Entity\Redirect\Redirect;
use App\Entity\Redirect\RedirectRepositoryInterface;
use App\Exception\RedirectException;
use App\Utils\UrlNormalizer;

final readonly class RedirectService
{
    public function __construct(
        private RedirectRepositoryInterface $redirectRepository,
        private RedirectValidator $redirectValidator,
    ) {
    }

    /**
     * Returns a redirect by its ID.
     *
     * @throws RedirectException
     */
    public function getById(int $id): Redirect
    {
        $redirect = $this->redirectRepository->findById($id);

        if ($redirect === null) {
            throw new RedirectException('error.redirect.not-found');
        }

        return $redirect;
    }

    /**
     * Returns an enabled redirect by its source URL.
     */
    public function resolve(string $url): ?Redirect
    {
        return $this->redirectRepository->findEnabledBySource(
            UrlNormalizer::normalize($url),
        );
    }

    /**
     * Creates or updates a redirect.
     *
     * @throws RedirectException
     */
    public function save(Redirect $redirect): void
    {
        $redirect->setSource(
            UrlNormalizer::normalize($redirect->getSource()),
        );

        $redirect->setTarget(
            UrlNormalizer::normalize($redirect->getTarget()),
        );

        $this->redirectValidator->validate($redirect);

        $this->redirectRepository->save($redirect);
    }

    /**
     * Deletes a redirect.
     */
    public function delete(Redirect $redirect): void
    {
        $this->redirectRepository->delete($redirect);
    }
}
