<?php
/**
 * Created by PhpStorm.
 * User: baturkacamak
 * Date: 26/2/23
 * Time: 8:22
 */

namespace Tera;

interface InstagramDataProviderInterface
{
    public function getInstagramData(string $username): array;
}
