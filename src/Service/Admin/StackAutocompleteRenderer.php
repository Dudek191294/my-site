<?php

namespace App\Service\Admin;

use App\Entity\Stack;
use Twig\Environment;

final class StackAutocompleteRenderer
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public function render(Stack $stack): string
    {
        return $this->twig->render('admin/autocomplete/stack.html.twig', [
            'entity' => $stack,
        ]);
    }
}
