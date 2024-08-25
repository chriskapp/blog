<?php

namespace Chriskapp\Blog\Controller;

use Chriskapp\Blog\Table;
use PSX\Api\Attribute\Get;
use PSX\Api\Attribute\Path;
use PSX\Framework\Config\ConfigInterface;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Framework\Loader\ReverseRouter;
use PSX\Http\Environment\HttpResponse;
use PSX\Sql\OrderBy;

class Feed extends ControllerAbstract
{
    public function __construct(private Table\Blog $blogTable, private ReverseRouter $reverseRouter, private ConfigInterface $config)
    {
    }

    #[Get]
    #[Path('/feed')]
    public function show(): mixed
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;

        $feed = $dom->createElement('feed');
        $feed->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');
        $dom->appendChild($feed);

        $feed->appendChild($dom->createElement('id', (string) $this->reverseRouter->getUrl([Index::class, 'show'])));
        $feed->appendChild($dom->createElement('title', $this->config->get('blog_title')));
        $feed->appendChild($dom->createElement('updated', $this->blogTable->getLastUpdated()->toString()));

        $link = $feed->appendChild($dom->createElement('link'));
        $link->setAttribute('rel', 'self');
        $link->setAttribute('href', (string) $this->reverseRouter->getUrl([Feed::class, 'show']));
        $link->setAttribute('type', 'application/atom+xml');

        $link = $feed->appendChild($dom->createElement('link'));
        $link->setAttribute('rel', 'alternate');
        $link->setAttribute('href', (string) $this->reverseRouter->getUrl([Index::class, 'show']));
        $link->setAttribute('type', 'text/html');

        $entries = $this->blogTable->findAll(startIndex: 0, count: 32, sortBy: Table\Generated\BlogTable::COLUMN_ID, sortOrder: OrderBy::DESC);
        foreach ($entries as $entry) {
            $entryElement = $feed->appendChild($dom->createElement('entry'));

            $url = (string) $this->reverseRouter->getUrl([Detail::class, 'show'], ['title' => $entry->getTitleSlug()]);

            $entryElement->appendChild($dom->createElement('id', $url));
            $entryElement->appendChild($dom->createElement('title', $entry->getTitle()));
            $entryElement->appendChild($dom->createElement('updated', $entry->getUpdated()->toString()));

            $linkElement = $dom->createElement('link');
            $linkElement->setAttribute('rel', 'alternate');
            $linkElement->setAttribute('href', $url);
            $linkElement->setAttribute('type', 'text/html');
            $entryElement->appendChild($linkElement);

            $authorElement = $dom->createElement('author');
            $authorElement->appendChild($dom->createElement('name', $entry->getAuthorName()));
            $authorElement->appendChild($dom->createElement('uri', $entry->getAuthorUri()));
            $entryElement->appendChild($authorElement);

            $summaryElement = $dom->createElement('summary', $entry->getSummary());
            $entryElement->appendChild($summaryElement);

            $categories = explode(',', $entry->getCategory());
            foreach ($categories as $category) {
                $categoryElement = $dom->createElement('category');
                $categoryElement->setAttribute('term', $category);
                $entryElement->appendChild($categoryElement);
            }

            $contentElement = $dom->createElement('content', $entry->getContent());
            $contentElement->setAttribute('type', 'html');
            $entryElement->appendChild($contentElement);
        }

        return new HttpResponse(
            200,
            ['Content-Type' => 'application/atom+xml'],
            $dom
        );
    }
}
