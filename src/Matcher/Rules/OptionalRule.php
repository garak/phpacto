<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2017  Damian Długosz
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

class OptionalRule extends AbstractRule
{
    /**
     * @var Rule
     */
    protected $rule;

    public function __construct(Rule $rule, $sample = null)
    {
        parent::__construct($sample);
    }

    public function getRule(): Rule
    {
        return $this->rule;
    }

    public function assertMatch($test): void
    {
        // If you are here, the key exists
        // If the key exists, the value must match the value
        $this->rule->assertMatch($test);
    }
}
