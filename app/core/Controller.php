<?php

declare(strict_types=1);

namespace Core;

/**
 * Base para os controladores.
 */
abstract class Controller
{
    protected function view(string $template, array $data = [], ?string $layout = 'layouts/site'): void
    {
        View::render($template, $data, $layout);
        Flash::clearOld();
    }

    protected function json(mixed $data, int $status = 200): never
    {
        json_response($data, $status);
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    protected function boolInput(string $key): int
    {
        return !empty($_POST[$key]) ? 1 : 0;
    }

    /**
     * Validação simples baseada em regras por campo.
     * Regras: required, email, numeric, min:N, max:N, in:a,b,c
     * @return array{0: bool, 1: array<string,string>}
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $ruleSet) {
            $value = trim((string) ($data[$field] ?? ''));
            foreach (explode('|', $ruleSet) as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                $fail = match ($name) {
                    'required' => $value === '',
                    'email'    => $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL),
                    'numeric'  => $value !== '' && !is_numeric($value),
                    'min'      => mb_strlen($value) < (int) $param,
                    'max'      => mb_strlen($value) > (int) $param,
                    'in'       => $value !== '' && !in_array($value, explode(',', (string) $param), true),
                    default    => false,
                };
                if ($fail) {
                    $errors[$field] = $this->message($field, $name, $param);
                    break;
                }
            }
        }
        return [$errors === [], $errors];
    }

    private function message(string $field, string $rule, ?string $param): string
    {
        $label = ucfirst(str_replace('_', ' ', $field));
        return match ($rule) {
            'required' => "O campo {$label} é obrigatório.",
            'email'    => "Informe um e-mail válido.",
            'numeric'  => "O campo {$label} deve ser numérico.",
            'min'      => "O campo {$label} deve ter ao menos {$param} caracteres.",
            'max'      => "O campo {$label} deve ter no máximo {$param} caracteres.",
            'in'       => "Valor inválido para {$label}.",
            default    => "O campo {$label} é inválido.",
        };
    }
}
