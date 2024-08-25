<?php

namespace Chriskapp\Blog\Service;

use Doctrine\DBAL\Schema\Schema;

class BlogSchema
{
    public static function build(Schema $schema): void
    {
        $table = $schema->createTable('app_blog');
        $table->addColumn('id', 'string', ['length' => 64]);
        $table->addColumn('title', 'string');
        $table->addColumn('title_slug', 'string');
        $table->addColumn('author_name', 'string', ['length' => 128]);
        $table->addColumn('author_uri', 'string');
        $table->addColumn('updated', 'datetime');
        $table->addColumn('summary', 'text');
        $table->addColumn('category', 'string');
        $table->addColumn('content', 'text');
        $table->setPrimaryKey(['id']);
    }
}
