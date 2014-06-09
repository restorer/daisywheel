<?php

namespace daisywheel\core;

class StringHelper
{
    public static function htmlEncode($str)
    {
        return htmlspecialchars(print_r($str, true), ENT_QUOTES | ENT_SUBSTITUTE | ENT_DISALLOWED, Context::CHARSET, true);
    }

    public static function htmlDecode($str)
    {
        return htmlspecialchars_decode(print_r($str, true), ENT_QUOTES);
    }

    public static function urlEncode($str, $raw=true)
    {
        if ($raw) {
            return rawurlencode(print_r($str, true));
        } else {
            return urlencode(print_r($str, true));
        }
    }

    public static function urlDecode($str, $raw=false)
    {
        if ($raw) {
            return rawurldecode(print_r($str, true));
        } else {
            return urldecode(print_r($str, true));
        }
    }
}
