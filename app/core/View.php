<?php

declare(strict_types=1);

namespace Core;

/**
 * Renderização de templates PHP com layout.
 */
final class View
{
    public static function render(string $template, array $data = [], ?string $layout = 'layouts/site'): void
    {
        $file = APP_PATH . '/views/' . $template . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View não encontrada: {$template}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $file;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = APP_PATH . '/views/' . $layout . '.php';
        if (!is_file($layoutFile)) {
            throw new \RuntimeException("Layout não encontrado: {$layout}");
        }
        require $layoutFile;
    }

    /** Renderiza uma partial e devolve como string. */
    public static function partial(string $template, array $data = []): string
    {
        $file = APP_PATH . '/views/' . $template . '.php';
        if (!is_file($file)) {
            return '';
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return ob_get_clean() ?: '';
    }
}
