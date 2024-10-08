<?php

namespace Chriskapp\Blog\Service;

class Slugify
{
    public function slugify(string $title): string
    {
        return strtolower(str_replace(' ', '-', $title));
    }
}
