<?php
/**
 * Created by PhpStorm.
 * User: baturkacamak
 * Date: 26/2/23
 * Time: 8:20
 */

namespace Tera;

class InstagramDataParser implements InstagramDataParserInterface
{
    public function __construct(
        private string $dataPrefix
    ) {
    }

    public function parseData(): array
    {
        $json = $this->extractJson();

        $entry_data = Arr::get($json, 'entry_data', []);
        $user       = Arr::get($entry_data, 'ProfilePage.0.graphql.user', []);
        $full_name  = Arr::get($user, 'full_name', '');
        $edges      = Arr::get($user, 'edge_owner_to_timeline_media.edges', []);

        $data = [];
        foreach ($edges as $edge) {
            $node                        = Arr::get($edge, 'node', []);
            $is_video                    = Arr::get($node, 'is_video', false);
            $shortcode                   = Arr::get($node, 'shortcode', '');
            $thumbnail_src               = Arr::get($node, 'thumbnail_src', '');
            $display_url                 = Arr::get($node, 'display_url', '');
            $taken_at_timestamp          = Arr::get($node, 'taken_at_timestamp', '');
            $edge_media_to_comment_count = Arr::get($node, 'edge_media_to_comment.count', '');
            $edge_liked_by_count         = Arr::get($node, 'edge_liked_by.count', '');

            if ($is_video) {
                $video_data = $this->extractVideoData('https://instagram.com/p/' . $shortcode);
                $video_url  = Arr::get($video_data, 'video_url', '');

                if ($video_url) {
                    $html = '<video width="100%" controls>' .
                            '<source src="' . $video_url . '" type="video/mp4">' .
                            '<img src="' . $display_url . '" alt="' . $shortcode . '">' .
                            '</video>';
                } else {
                    $html = '';
                }
                $type = 'video';
            } else {
                $html = '<img src="' . $display_url . '" alt="' . $shortcode . '">';
                $type = 'image';
            }

            $data[] = [
                'full_name'                   => $full_name,
                'thumbnail_src'               => $thumbnail_src,
                'display_url'                 => $display_url,
                'taken_at_timestamp'          => $taken_at_timestamp,
                'edge_media_to_comment_count' => $edge_media_to_comment_count,
                'edge_liked_by_count'         => $edge_liked_by_count,
                'type'                        => $type,
                'html'                        => $html,
            ];
        }

        return $data;
    }

    private function extractJson(): array
    {
        $start = strpos($this->dataPrefix, 'window._sharedData = ') + strlen('window._sharedData = ');
        $end   = strpos($this->dataPrefix, ';</script>', $start);

        return json_decode(substr($this->dataPrefix, $start, $end - $start), true);
    }

    private function extractVideoData(string $url): array
    {
        $data  = wp_remote_retrieve_body(wp_remote_get($url));
        $start = strpos($data, 'window.__additionalDataLoaded') + strlen('window.__additionalDataLoaded(');
        $end   = strrpos($data, ');</script>');
        $json  = substr($data, $start, $end - $start);

        return json_decode($json, true);
    }

    public function parse(string $html): array
    {
        $shards = explode($this->dataPrefix . ' = ', $html);
        $json   = explode(';</script>', $shards[1]);
        $data   = json_decode($json[0], true);

        return $data;
    }
}
