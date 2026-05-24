<?php

namespace Symfony\Component\HttpFoundation;

if (!function_exists(__NAMESPACE__.'\\request_parse_body')) {
    function request_parse_body(): array
    {
        return [$_POST, $_FILES];
    }
}
