<?php
/**
 * Created by PhpStorm.
 * User: baturkacamak
 * Date: 26/2/23
 * Time: 8:14
 */

namespace Tera;

class PostHtmlBuilder implements PostHtmlBuilderInterface
{
    private $thumbnailWidths;

    public function __construct(array $thumbnailWidths)
    {
        $this->thumbnailWidths = $thumbnailWidths;
    }

    public function buildHtml(array $data): string
    {
        $html = '';

        foreach ($data as $post) {
            if ($post['type'] === 'video') {
                $html .= $this->buildVideoHtml($post);
            } else {
                $html .= $this->buildImageHtml($post);
            }
        }

        return $html;
    }

    private function buildImageHtml(array $post): string
    {
        $pictureHtml = $this->buildPictureHtml($post);

        return sprintf(
            '<figure>%s<figcaption>%s</figcaption></figure>',
            $pictureHtml,
            $post['full_name']
        );
    }

    private function buildVideoHtml(array $post): string
    {
        $videoHtml = $this->buildVideoElementHtml($post);
        $pictureHtml = $this->buildPictureHtml($post, false);

        return sprintf(
            '<figure>%s%s<figcaption>%s</figcaption></figure>',
            $videoHtml,
            $pictureHtml,
            $post['full_name']
        );
    }

    private function buildPictureHtml(array $post, bool $includeSource = true): string
    {
        $thumbnails = $post['thumbnails'];
        $sourceHtml = '';

        if ($includeSource) {
            foreach ($this->thumbnailWidths as $width) {
                $thumbnailSrc = isset($thumbnails[$width]) ? $thumbnails[$width] : null;

                if ($thumbnailSrc) {
                    $sourceHtml .= sprintf(
                        '<source srcset="%s" media="(min-width: %spx)">',
                        $thumbnailSrc,
                        $width
                    );
                }
            }
        }

        $imgHtml = sprintf(
            '<img src="%s" alt="%s">',
            $post['original'],
            $post['description']
        );

        return sprintf(
            '<picture>%s%s</picture>',
            $sourceHtml,
            $imgHtml
        );
    }

    private function buildVideoElementHtml(array $post): string
    {
        $sourceHtml = sprintf(
            '<source src="%s" type="video/mp4">',
            $post['original']
        );

        return sprintf(
            '<video controls>%s</video>',
            $sourceHtml
        );
    }
}
