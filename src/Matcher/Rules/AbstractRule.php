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

namespace Bigfoot\PHPacto\Matcher\Rules;

abstract class AbstractRule implements Rule
{
    protected $value;
    protected $sample;

    public function __construct($value, $sample = null)
    {
        $this->value = $value;
        $this->sample = $sample;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getSample()
    {
        return $this->sample;
    }

    /**
     * Throws exception if an unsupported value is provided.
     *
     * @param $value
     *
     * @throws \InvalidArgumentException
     */
    abstract protected function assertSupport($value): void;
}