<?php

namespace App\Service\Icon;

/**
 * Result of importing one or more Simple Icons into public/icons/stack/.
 */
final class SimpleIconImportReport
{
    /** @param list<string> $added */
    /** @param list<string> $skipped */
    /** @param list<string> $errors */
    public function __construct(
        private readonly array $added = [],
        private readonly array $skipped = [],
        private readonly array $errors = [],
    ) {
    }

    /**
     * @return list<string>
     */
    public function added(): array
    {
        return $this->added;
    }

    /**
     * @return list<string>
     */
    public function skipped(): array
    {
        return $this->skipped;
    }

    /**
     * @return list<string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    public function firstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    public function merge(self $other): self
    {
        return new self(
            [...$this->added, ...$other->added],
            [...$this->skipped, ...$other->skipped],
            [...$this->errors, ...$other->errors],
        );
    }
}
