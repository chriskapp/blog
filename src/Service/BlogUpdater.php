<?php

namespace Chriskapp\Blog\Service;

use Chriskapp\Blog\Controller\Detail;
use Chriskapp\Blog\Table\Blog;
use Chriskapp\Blog\Table\Generated\BlogRow;
use PSX\DateTime\LocalDateTime;
use PSX\Framework\Config\ConfigInterface;
use PSX\Framework\Loader\ReverseRouter;

class BlogUpdater
{
    public function __construct(private ConfigInterface $config, private Slugify $slugify, private Blog $blogTable, private ReverseRouter $reverseRouter)
    {
    }

    public function updateAll(bool $force = false): \Generator
    {
        $dom = new \DOMDocument();
        $dom->load($this->config->get('blog_file'));

        foreach ($dom->getElementsByTagName('entry') as $entry) {
            if (!$entry instanceof \DOMElement) {
                continue;
            }

            $title = $entry->getElementsByTagName('title')->item(0)?->textContent ?? throw new \RuntimeException('Provided no title');

            $slug = $this->slugify->slugify($title);

            $id = (string) $this->reverseRouter->getUrl([Detail::class, 'show'], ['title' => $slug]);

            $updated = LocalDateTime::parse($entry->getElementsByTagName('updated')->item(0)?->textContent ?? throw new \RuntimeException('Provided no updated'));

            $summary = trim($entry->getElementsByTagName('summary')->item(0)?->textContent ?? throw new \RuntimeException('Provided no summary'));
            $content = trim($entry->getElementsByTagName('content')->item(0)?->textContent ?? throw new \RuntimeException('Provided no content'));

            $categories = [];
            foreach ($entry->getElementsByTagName('category') as $categoryElement) {
                $categories[] = trim($categoryElement->getAttribute('term'));
            }

            $authorName = $this->config->get('blog_author_name');
            $authorUri = $this->config->get('blog_author_uri');

            $row = $this->blogTable->findOneById($id);
            if (!$row instanceof BlogRow) {
                // the blog entry does not exist create it
                $row = new BlogRow();
                $row->setId($id);
                $row->setTitle($title);
                $row->setTitleSlug($slug);
                $row->setAuthorName($authorName);
                $row->setAuthorUri($authorUri);
                $row->setUpdated($updated);
                $row->setSummary($summary);
                $row->setCategory(implode(',', $categories));
                $row->setContent($content);
                $this->blogTable->create($row);

                yield 'Created ' . $title;
            } else {
                if ($force || $updated > $row->getUpdated()) {
                    // if the update date has change update the entry
                    $row = new BlogRow();
                    $row->setId($id);
                    $row->setTitle($title);
                    $row->setTitleSlug($slug);
                    $row->setAuthorName($authorName);
                    $row->setAuthorUri($authorUri);
                    $row->setUpdated($updated);
                    $row->setSummary($summary);
                    $row->setCategory(implode(',', $categories));
                    $row->setContent($content);
                    $this->blogTable->update($row);

                    yield 'Updated ' . $title;
                }
            }
        }
    }
}
