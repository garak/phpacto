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

use Bigfoot\PHPacto\Matcher\Mismatches;

class OrRule extends AbstractRule
{
    public function __construct($value)
    {
        $this->assertSupport($value);

        parent::__construct($value);
    }

    public function assertMatch($test): void
    {
        $mismatches = [];

        /** @var Rule $item */
        foreach ($this->value as $item) {
            try {
                $item->assertMatch($test);

                // If at least one item match the value, its OK
                return;
            } catch (Mismatches\Mismatch $e) {
                $mismatches[] = $e;
            }
        }

        if (count($mismatches) === count($this->value)) {
            throw new Mismatches\MismatchCollection($mismatches, 'None of the {{ count }} rules is matching');
        }
    }

    public function getSample()
    {
        if (count($this->value)) {
            /** @var Rule $rule */
            $rule = $this->value[array_rand($this->value)];

            return $rule->getSample();
        }
    }

    protected function assertSupport($value): void
    {
        if (!is_array($value)) {
            throw new Mismatches\TypeMismatch('array', gettype($value));
        }

        foreach ($value as $item) {
            if (!$item instanceof Rule) {
                throw new Mismatches\TypeMismatch('Rule', gettype($value), 'Each item should be an instance of {{ expected }}');
            }
        }
    }
}