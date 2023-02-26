<?php
/**
 * Created by PhpStorm.
 * User: baturkacamak
 * Date: 26/2/23
 * Time: 8:19
 */

namespace Tera;

interface InstagramDataParserInterface
{
    public function parse(string $html): array;
}
