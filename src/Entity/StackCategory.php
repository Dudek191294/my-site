<?php

namespace App\Entity;

enum StackCategory: string
{
    case Frontend = 'frontend';
    case Backend = 'backend';
    case Database = 'database';
    case Infrastructure = 'infrastructure';
    case Testing = 'testing';
    case Tools = 'tools';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Frontend => 'Frontend',
            self::Backend => 'Backend',
            self::Database => 'Baza danych',
            self::Infrastructure => 'Infrastruktura',
            self::Testing => 'Testy',
            self::Tools => 'Narzędzia',
            self::Other => 'Inne',
        };
    }
}
