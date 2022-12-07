<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

declare(strict_types=1);

namespace block_dedication\local\filters;

use core_reportbuilder\local\helpers\database;
use core_reportbuilder\local\filters\select as core_select;

/**
 * Select report filter, slightly altered for block_dedication
 *
 * @package     block_dedication
 * @copyright   2022 Michael Kotlyar <michael.kotlyar@catalyst.net.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class select extends core_select {

    /**
     * Return filter SQL
     *
     * Note that operators must be of type integer, while values can be integer or string.
     *
     * Modified to search "array"s of values in a column string value.
     *
     * @param array $values
     * @return array array of two elements - SQL query and named parameters
     */
    public function get_sql_filter(array $values): array {
        global $DB;
        $name = database::generate_param_name();

        $operator = $values["{$this->name}_operator"] ?? self::ANY_VALUE;
        $value = $values["{$this->name}_value"] ?? 0;

        $fieldsql = $this->filter->get_field_sql();
        $params = $this->filter->get_field_params();

        // Validate filter form values.
        if (!$this->validate_filter_values((int) $operator, $value)) {
            // Filter configuration is invalid. Ignore the filter.
            return ['', []];
        }

        $value = "%,$value,%";

        switch ($operator) {
            case self::EQUAL_TO:
                $fieldsql = $DB->sql_like($fieldsql, ":$name");
                $params[$name] = $value;
                break;
            case self::NOT_EQUAL_TO:
                $fieldsql = $DB->sql_like($fieldsql, ":$name", true, true, true);
                $params[$name] = $value;
                break;
            default:
                return ['', []];
        }
        return [$fieldsql, $params];
    }

    /**
     * Validate filter form values
     *
     * @param int|null $operator
     * @param mixed|null $value
     * @return bool
     */
    private function validate_filter_values(?int $operator, $value): bool {
        return !($operator === null || $value === '');
    }
}
