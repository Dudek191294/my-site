<?php

namespace App\Dto\Admin;

use App\Entity\StackCategory;
use Symfony\Component\Validator\Constraints as Assert;

final class StackQuickCreateInput
{
    #[Assert\NotBlank(message: 'Podaj nazwę technologii.')]
    #[Assert\Length(max: 120)]
    public string $name = '';

    #[Assert\NotNull]
    public ?StackCategory $category = StackCategory::Tools;

    #[Assert\Regex(pattern: '/^[a-z0-9]{1,80}$/', message: 'Ikona musi być slugiem Simple Icons (tylko a–z, 0–9).')]
    public ?string $icon = null;
}
