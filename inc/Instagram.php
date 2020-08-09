<?php

namespace Tera;

use Illuminate\Support\Arr;
use mysql_xdevapi\Exception;
use WP_Error;

class Instagram
{
    public function __construct()
    {
        $this->username = null;

        $this->register();
    }

    public function register()
    {
        add_action('wp_ajax_get_instagram_feed', [$this, 'scrapeInstagram']);
    }


    public function scrapeInstagram()
    {
        if (!defined('DOING_AJAX') && !DOING_AJAX || !isset($_POST['username'])) {
            return false;
        }

        try {
            $this->setUsername($_POST['username']);
        } catch (\Exception $exception) {
            throw $exception;
        }

        $transient_id = 'instagram-' . sanitize_title_with_dashes($this->username);

        $instagram = new \stdClass();
        $transient_data = unserialize(json_decode(get_transient($transient_id)));
        if (false === ($instagram->data = $transient_data)) {
            $arr_instagram = $this->getInstagramArray("https://instagram.com/{$this->username}");

            $user_full_name = Arr::get(
                $arr_instagram,
                'entry_data.ProfilePage.0.graphql.user.full_name',
                $this->username
            );

            $arr_user = Arr::get($arr_instagram, 'entry_data.ProfilePage.0.graphql.user', false);

            if ($arr_user) {
                $images = Arr::get($arr_user, 'edge_owner_to_timeline_media.edges', false);
            } else {
                return new WP_Error(
                    'bad_json_2',
                    esc_html__(
                        'Instagram has returned invalid data.',
                        'mariselle'
                    )
                );
            }

            if (!is_array($images)) {
                return new WP_Error(
                    'bad_array',
                    esc_html__(
                        'Instagram has returned invalid data.',
                        'mariselle'
                    )
                );
            }


            foreach ($images as $image) {
                $image_node = Arr::get($image, 'node', false);
                if (!$image_node) {
                    continue;
                }
                $image['thumbnail_src'] = Arr::get($image_node, 'thumbnail_src', false);

                $image['display_url'] = Arr::get($image_node, 'display_url', false);

                $thumbnail_resources = Arr::get($image_node, 'thumbnail_resources', false);
                $image_srcset = '';
                foreach ($thumbnail_resources as $index => $thumbnail_resource) {
                    $config_width = Arr::get($thumbnail_resource, 'config_width', false);
                    $config_height = Arr::get($thumbnail_resource, 'config_height', false);
                    $thumbnail_src = Arr::get($thumbnail_resource, 'src', false);
                    if ($config_width && $config_height && $thumbnail_src) {
                        $image['thumbnails'][$config_height . 'x' . $config_width] = $thumbnail_src;
                        $image_srcset .= "{$thumbnail_src} {$config_width}w";
                        if (count($thumbnail_resources) - 1 > $index) {
                            $image_srcset .= ',';
                        }
                    }
                }

                $caption = Arr::get(
                    $image_node,
                    'edge_media_to_caption.edges.0.node.text',
                    false
                );

                $html = '';
                $instagram_post_link = '//instagram.com/p/' . Arr::get($image_node, 'shortcode', false);
                if (
                    true === Arr::get($image, 'node.is_video', false) || true === Arr::get(
                        $image,
                        'node.edge_sidecar_to_children.edges.0.node.is_video',
                        false
                    )
                ) {
                    $type = 'video';
                    $insta_array_video = $this->getInstagramArray(
                        'https:' . $instagram_post_link
                    );

                    $video_url = Arr::get(
                        $insta_array_video,
                        'entry_data.PostPage.0.graphql.shortcode_media.video_url',
                        false
                    );

                    if (!$video_url) {
                        $video_url = Arr::get(
                            $insta_array_video,
                            'entry_data.PostPage.0.graphql.shortcode_media.edge_sidecar_to_children.edges.0.node.video_url',
                            false
                        );
                    }

                    if ($video_url) {
                        ob_start(); ?>
                        <video width="100%" controls>
                            <source src="<?php
                            echo $video_url; ?>" type="video/mp4">
                            <img src="<?php
                            echo $image['display_url']; ?>" srcset="<?php
                            echo $image_srcset; ?>" alt="<?php
                            echo $caption; ?>">
                        </video>
                        <?php
                        $html = trim(ob_get_clean());
                    }
                } else {
                    $type = 'image';
                    ob_start(); ?>
                    <img src="<?php
                    echo $image['display_url']; ?>" srcset="<?php
                    echo $image_srcset; ?>" alt="<?php
                    echo isset($caption) ? $caption : ''; ?>"/>
                    <?php
                    $html = trim(ob_get_clean());
                }


                $instagram->data[] = [
                    'user' => $this->username,
                    'full_name' => $user_full_name,
                    'description' => $caption,
                    'link' => $instagram_post_link,
                    'time' => Arr::get($image_node, 'taken_at_timestamp', ''),
                    'comments' => Arr::get($image_node, 'edge_media_to_comment.count', ''),
                    'likes' => Arr::get($image_node, 'edge_liked_by.count', ''),
                    'thumbnails' => $image['thumbnails'],
                    'original' => $image['display_url'],
                    'type' => $type,
                    'html' => $html,
                ];
            }

            // Do not set an empty transient - should help catch private or empty accounts.
            if (!empty($instagram->data)) {
                $instagram->json = json_encode(serialize($instagram->data));
                $cache_minutes = intval(120);
                set_transient(
                    $transient_id,
                    $instagram->json,
                    MINUTE_IN_SECONDS * $cache_minutes
                );
            }
        }

        if (!empty($instagram->data)) {
            echo json_encode(array_slice($instagram->data, 0, 3));
            exit();
        } else {
            return new WP_Error(
                'no_images',
                esc_html__(
                    'Instagram did not return any images.',
                    'mariselle'
                )
            );
        }
    }

    /**
     * @param null $username
     */
    public function setUsername($username)
    {
        $username_temp = sanitize_text_field($username);

        if (!$username_temp || empty($username_temp)) {
            throw new Exception('Username can not be empty');
        }

        // $username, $slice = 9, $options = [];
        $username_temp = trim(strtolower($username_temp));
        $username_temp = str_replace('@', '', $username_temp);

        $this->username = $username_temp;
    }

    /**
     * @param        $url
     * @param string $explode
     *
     * @return mixed|WP_Error
     */

    private function getInstagramArray($url, $explode = 'window._sharedData')
    {
        $remote = wp_remote_get($url);

        if (is_wp_error($remote)) {
            return new WP_Error(
                'site_down',
                esc_html__(
                    'Unable to communicate with Instagram.',
                    'mariselle'
                )
            );
        }
        if (200 !== wp_remote_retrieve_response_code($remote)) {
            return new WP_Error(
                'invalid_response',
                esc_html__(
                    'Instagram did not return a 200.',
                    'mariselle'
                )
            );
        }

        $shards = explode($explode . ' = ', $remote['body']);
        $insta_json = explode(';</script>', $shards[1]);
        $insta_array = json_decode($insta_json[0], true);

        if (!$insta_array) {
            return new WP_Error(
                'bad_json',
                esc_html__(
                    'Instagram has returned invalid data.',
                    'mariselle'
                )
            );
        }

        return $insta_array;
    }

    private function setTransient()
    {
    }
}
