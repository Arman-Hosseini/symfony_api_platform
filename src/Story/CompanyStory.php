<?php

namespace App\Story;

use App\Factory\CompanyFactory;
use Zenstruck\Foundry\Story;

final class CompanyStory extends Story
{
    public function build(): void
    {
        CompanyFactory::createMany(10);
    }
}
