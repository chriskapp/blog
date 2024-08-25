<?php

namespace Chriskapp\Blog\Controller;

use Chriskapp\Blog\Table;
use Chriskapp\Blog\Table\Generated\BlogRow;
use PSX\Api\Attribute\Get;
use PSX\Api\Attribute\Param;
use PSX\Api\Attribute\Path;
use PSX\Framework\Config\ConfigInterface;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Framework\Http\Writer\Template;
use PSX\Framework\Loader\ReverseRouter;
use PSX\Http\Exception as StatusCode;

class Detail extends ControllerAbstract
{
    public function __construct(private Table\Blog $blogTable, private ReverseRouter $reverseRouter, private ConfigInterface $config)
    {
    }

    #[Get]
    #[Path('/blog/:title')]
    public function show(#[Param] string $title): mixed
    {
        $entry = $this->blogTable->findOneByTitleSlug($title);
        if (!$entry instanceof BlogRow) {
            throw new StatusCode\NotFoundException('Entry not found');
        }

        $data = [
            'title' => $entry->getTitle(),
            'canonical' => $this->reverseRouter->getUrl([self::class, 'show'], [$entry->getTitleSlug()]),
            'entry' => $entry,
        ];

        $templateFile = __DIR__ . '/../../../../../resources/template/' . $this->config->get('blog_template_detail');
        return new Template($data, $templateFile, $this->reverseRouter);
    }
}
