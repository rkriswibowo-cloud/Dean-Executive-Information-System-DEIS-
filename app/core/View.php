<?php
namespace App\Core;

class View {
    public static function render(string $viewPath, array $data = [], string $layout = 'layouts/main'): void {
        // Extract data variables into current scope
        extract($data);

        // App config helper
        $appConfig = require __DIR__ . '/../config/app.php';
        $baseUrl = $appConfig['base_url'];

        // Buffer view content
        $viewFile = __DIR__ . '/../views/' . $viewPath . '.php';
        if (!file_exists($viewFile)) {
            echo "<h1>View Error</h1><p>View file not found: <code>{$viewPath}</code></p>";
            return;
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Render layout if specified
        if ($layout) {
            $layoutFile = __DIR__ . '/../views/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
                return;
            }
        }

        // Direct output if no layout
        echo $content;
    }
}
