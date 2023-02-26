<?php
/**
 * Created by PhpStorm.
 * User: baturkacamak
 * Date: 26/2/23
 * Time: 8:13
 */

namespace Tera;

interface PostHtmlBuilderInterface
{
    public function buildHtml(array $data): string;
}
