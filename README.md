
# Blog

Simple blog engine

## Installation

The blog engine uses [PSX](https://github.com/apioo/psx) to install it you only need at this package:

```
composer require chriskapp/blog
```

## Configuration

Then you need to add the following configurations at the `configuration.php` file:

```php
return [
    // the blog source xml file containing all posts 
    'blog_file'               => __DIR__ . '/resources/blog.xml',
    'blog_title'              => 'chrisk.app',

    // the default author of the blog posts
    'blog_author_name'        => 'chriskapp',
    'blog_author_uri'         => 'https://chrisk.app/',

    // the blog template files
    'blog_template_index'     => 'blog.php',
    'blog_template_detail'    => 'blog/detail.php',

    // ...
];
```

And you need to add the `container.php` file to the container builder:

```php
return \PSX\Framework\Dependency\ContainerBuilder::build(
    __DIR__,
    true,
    __DIR__ . '/vendor/psx/framework/resources/container.php',
    __DIR__ . '/vendor/chriskapp/blog/resources/container.php',
    __DIR__ . '/resources/container.php',
);

```
