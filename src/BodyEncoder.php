<?php

/*
 * This file is part of PHPacto
 * Copyright (C) 2017  Damian Długosz
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Bigfoot\PHPacto;

use Bigfoot\PHPacto\Matcher\Mismatches\TypeMismatch;

abstract class BodyEncoder
{
    public static function encode($body, string $contentType): string
    {
        $isJson = false !== stripos($contentType, 'application/json');

        if ($isJson) {
            return json_encode($body);
        } elseif (is_array($body)) {
            return http_build_query($body);
        }

        return (string) $body;
    }

    public static function decode(string $body, ?string $contentType)
    {
        if (false !== strpos($contentType, 'application/json')) {
            return static::decodeJsonEncoded($body);
        } elseif (false !== stripos($contentType, 'application/x-www-form-urlencoded') || false !== strpos($contentType, 'multipart/form-data')) {
            return static::decodeUrlEncoded($body);
        }

        return $body;
    }

    protected static function decodeUrlEncoded(string $body)
    {
        $decoded = [];
        parse_str($body, $decoded);

        return $decoded;
    }

    protected static function decodeJsonEncoded(string $body)
    {
        $decoded = json_decode($body, true);

        if (null === $decoded) {
            throw new TypeMismatch('json', 'string', 'Body content is not a valid JSON');
        }

        return $decoded;
    }
}