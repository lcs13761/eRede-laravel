<?php

declare(strict_types=1);

namespace Lcs13761\EredeLaravel\Enums;

enum HttpMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
}