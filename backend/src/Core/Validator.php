<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    /**
     * @param array<string, scalar|null> $data
     * @param array<string, string> $rules field => pipe-separated rules, e.g. "required|email|max:191"
     * @return array{errors: array<string, string>}
     */
    public static function check(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $ruleString) {
            $raw = $data[$field] ?? null;
            $str = $raw === null || is_scalar($raw) ? trim((string) $raw) : '';

            $parts = array_values(array_filter(
                array_map(static fn (string $s): string => trim($s), explode('|', $ruleString)),
                static fn (string $s): bool => $s !== ''
            ));
            $hasRequired = in_array('required', $parts, true);

            if (!$hasRequired && $str === '') {
                continue;
            }
            if ($hasRequired && $str === '') {
                $errors[$field] = 'This field is required.';
                continue;
            }

            foreach ($parts as $rule) {
                if ($rule === 'required') {
                    continue;
                }
                if (str_starts_with($rule, 'max:')) {
                    $n = (int) substr($rule, 4);
                    if (mb_strlen($str) > $n) {
                        $errors[$field] = "Must be no more than {$n} characters.";
                        break;
                    }
                    continue;
                }
                if (str_starts_with($rule, 'min:')) {
                    $n = (int) substr($rule, 4);
                    if (mb_strlen($str) < $n) {
                        $errors[$field] = "Must be at least {$n} characters.";
                        break;
                    }
                    continue;
                }
                if ($rule === 'email' && !filter_var($str, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = 'Enter a valid email address.';
                    break;
                }
            }
        }

        return ['errors' => $errors];
    }
}
