<?php

namespace Hafo\Facebook;

use Nette\Utils\Image;

final class FacebookAvatars {

    function download($fbid, array $params = []) {
        $query = http_build_query($params);
        $data = file_get_contents('http://graph.facebook.com/' . $fbid . '/picture?' . $query);
        if($data) {
            return Image::fromString($data);
        }
        return NULL;
    }

}
