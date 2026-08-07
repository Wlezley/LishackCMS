<?php

declare(strict_types=1);

namespace App\Service\Redirect;

use App\Entity\Redirect\Redirect;
use App\Entity\Redirect\RedirectRepositoryInterface;
use App\Exception\RedirectException;

final readonly class RedirectValidator
{
    public function __construct(
        private RedirectRepositoryInterface $redirectRepository,
    ) {
    }

    /**
     * Validates a redirect entity.
     *
     * @throws RedirectException
     */
    public function validate(Redirect $redirect): void
    {
        $this->validateSource($redirect->getSource());
        $this->validateTarget($redirect->getTarget());
        $this->validateLoop($redirect->getSource(), $redirect->getTarget());
        $this->validateUniqueSource($redirect);
    }

    /**
     * @throws RedirectException
     */
    public function validateSource(string $source): void
    {
        if ($source === '') {
            throw new RedirectException('Redirect source cannot be empty.');
        }
    }

    /**
     * @throws RedirectException
     */
    public function validateTarget(string $target): void
    {
        if ($target === '') {
            throw new RedirectException('Redirect target cannot be empty.');
        }
    }

    /**
     * @throws RedirectException
     */
    public function validateLoop(string $source, string $target): void
    {
        if ($source === $target) {
            throw new RedirectException('Redirect source and target cannot be the same.');
        }
    }

    /**
     * @throws RedirectException
     */
    public function validateUniqueSource(Redirect $redirect): void
    {
        $existing = $this->redirectRepository->findBySource($redirect->getSource());

        if ($existing === null) {
            return;
        }

        if (
            $redirect->isPersisted()
            && $existing->getId() === $redirect->getId()
        ) {
            return;
        }

        throw new RedirectException(
            sprintf(
                'Redirect "%s" already exists.',
                $redirect->getSource(),
            ),
        );
    }
}
