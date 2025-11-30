<?php

namespace App\Services;

use App\Models\Screenshot;
use App\Models\Template;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Servicio de Renderizado de Mockups
 * Combina screenshots con templates y textos para generar imágenes finales
 */
class MockupRenderService
{
    /**
     * Renderiza un mockup completo
     *
     * @param Screenshot $screenshot El screenshot a usar
     * @param Template $template El template a aplicar
     * @param array $texts Los textos a superponer
     * @param string $language El idioma de los textos
     * @param string $format El formato de salida (png, jpeg, webp)
     * @return array Información del archivo generado
     */
    public function render(
        Screenshot $screenshot,
        Template $template,
        array $texts,
        string $language,
        string $format = 'png'
    ): array {
        $config = $template->layout_config;

        // Dimensiones del canvas
        $width = $config['canvas']['width'] ?? 1200;
        $height = $config['canvas']['height'] ?? 628;

        // Crear imagen base
        $canvas = imagecreatetruecolor($width, $height);

        // Habilitar alpha blending
        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);

        // Color de fondo
        $bgColor = $this->hexToRgb($config['canvas']['background'] ?? '#ffffff');
        $background = imagecolorallocate($canvas, $bgColor['r'], $bgColor['g'], $bgColor['b']);
        imagefill($canvas, 0, 0, $background);

        // Aplicar gradiente si está configurado
        if (isset($config['canvas']['gradient'])) {
            $this->applyGradient($canvas, $config['canvas']['gradient']);
        }

        // Cargar y posicionar screenshot
        $screenshotImage = $this->loadImage($screenshot->full_path);
        if ($screenshotImage) {
            $this->placeScreenshot($canvas, $screenshotImage, $config['screenshot'] ?? []);
            imagedestroy($screenshotImage);
        }

        // Renderizar textos
        $this->renderTexts($canvas, $texts, $config['texts'] ?? []);

        // Aplicar efectos finales si están configurados
        if (isset($config['effects'])) {
            $this->applyEffects($canvas, $config['effects']);
        }

        // Guardar imagen
        $filename = Str::uuid() . '.' . $format;
        $path = "renders/{$screenshot->project_id}/" . $filename;
        $fullPath = Storage::disk('public')->path($path);

        // Asegurar que el directorio existe
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Guardar según formato
        switch ($format) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($canvas, $fullPath, 90);
                break;
            case 'webp':
                imagewebp($canvas, $fullPath, 85);
                break;
            default:
                imagepng($canvas, $fullPath, 8);
        }

        $fileSize = filesize($fullPath);
        imagedestroy($canvas);

        return [
            'path' => $path,
            'format' => $format,
            'width' => $width,
            'height' => $height,
            'file_size' => $fileSize,
        ];
    }

    /**
     * Genera una preview en base64 sin guardar
     */
    public function generatePreview(
        Screenshot $screenshot,
        Template $template,
        array $texts
    ): string {
        $config = $template->layout_config;

        // Preview a menor resolución
        $width = ($config['canvas']['width'] ?? 1200) / 2;
        $height = ($config['canvas']['height'] ?? 628) / 2;

        $canvas = imagecreatetruecolor((int) $width, (int) $height);
        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);

        $bgColor = $this->hexToRgb($config['canvas']['background'] ?? '#ffffff');
        $background = imagecolorallocate($canvas, $bgColor['r'], $bgColor['g'], $bgColor['b']);
        imagefill($canvas, 0, 0, $background);

        // Cargar screenshot
        $screenshotImage = $this->loadImage($screenshot->full_path);
        if ($screenshotImage) {
            $scaledConfig = $this->scaleConfig($config['screenshot'] ?? [], 0.5);
            $this->placeScreenshot($canvas, $screenshotImage, $scaledConfig);
            imagedestroy($screenshotImage);
        }

        // Renderizar textos
        $scaledTextsConfig = $this->scaleConfig($config['texts'] ?? [], 0.5);
        $this->renderTexts($canvas, $texts, $scaledTextsConfig);

        // Convertir a base64
        ob_start();
        imagepng($canvas, null, 6);
        $imageData = ob_get_clean();
        imagedestroy($canvas);

        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    /**
     * Carga una imagen desde archivo
     */
    protected function loadImage(string $path)
    {
        if (!file_exists($path)) {
            return null;
        }

        $info = getimagesize($path);
        if (!$info) {
            return null;
        }

        return match ($info[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            IMAGETYPE_GIF => imagecreatefromgif($path),
            default => null,
        };
    }

    /**
     * Posiciona el screenshot en el canvas
     */
    protected function placeScreenshot($canvas, $screenshot, array $config): void
    {
        $srcWidth = imagesx($screenshot);
        $srcHeight = imagesy($screenshot);

        $destX = $config['x'] ?? 0;
        $destY = $config['y'] ?? 0;
        $destWidth = $config['width'] ?? $srcWidth;
        $destHeight = $config['height'] ?? $srcHeight;

        // Aplicar sombra si está configurada
        if (isset($config['shadow']) && $config['shadow']['enabled']) {
            $this->applyShadow($canvas, $destX, $destY, $destWidth, $destHeight, $config['shadow']);
        }

        // Redimensionar y copiar
        imagecopyresampled(
            $canvas,
            $screenshot,
            (int) $destX,
            (int) $destY,
            0,
            0,
            (int) $destWidth,
            (int) $destHeight,
            $srcWidth,
            $srcHeight
        );

        // Aplicar borde redondeado si está configurado
        if (isset($config['border_radius']) && $config['border_radius'] > 0) {
            $this->applyBorderRadius($canvas, $destX, $destY, $destWidth, $destHeight, $config['border_radius']);
        }
    }

    /**
     * Renderiza los textos en el canvas
     */
    protected function renderTexts($canvas, array $texts, array $config): void
    {
        $fontPath = resource_path('fonts/Inter-Regular.ttf');

        // Si no existe la fuente, usar una por defecto
        if (!file_exists($fontPath)) {
            // Renderizar con texto básico de GD
            foreach ($texts as $key => $text) {
                if (empty($text) || !isset($config[$key])) {
                    continue;
                }

                $textConfig = $config[$key];
                $color = $this->hexToRgb($textConfig['color'] ?? '#000000');
                $textColor = imagecolorallocate($canvas, $color['r'], $color['g'], $color['b']);

                $x = $textConfig['x'] ?? 10;
                $y = $textConfig['y'] ?? 10;

                imagestring($canvas, 5, (int) $x, (int) $y, $text, $textColor);
            }
            return;
        }

        foreach ($texts as $key => $text) {
            if (empty($text) || !isset($config[$key])) {
                continue;
            }

            $textConfig = $config[$key];
            $fontSize = $textConfig['size'] ?? 24;
            $color = $this->hexToRgb($textConfig['color'] ?? '#000000');
            $textColor = imagecolorallocate($canvas, $color['r'], $color['g'], $color['b']);

            $x = $textConfig['x'] ?? 10;
            $y = $textConfig['y'] ?? 10;
            $maxWidth = $textConfig['max_width'] ?? null;

            // Wordwrap si hay límite de ancho
            if ($maxWidth) {
                $text = $this->wordWrapText($text, $fontPath, $fontSize, $maxWidth);
            }

            imagettftext($canvas, $fontSize, 0, (int) $x, (int) $y, $textColor, $fontPath, $text);
        }
    }

    /**
     * Aplica un gradiente al canvas
     */
    protected function applyGradient($canvas, array $gradient): void
    {
        $width = imagesx($canvas);
        $height = imagesy($canvas);

        $startColor = $this->hexToRgb($gradient['start'] ?? '#ffffff');
        $endColor = $this->hexToRgb($gradient['end'] ?? '#000000');
        $direction = $gradient['direction'] ?? 'vertical';

        for ($i = 0; $i < ($direction === 'vertical' ? $height : $width); $i++) {
            $ratio = $i / ($direction === 'vertical' ? $height : $width);

            $r = (int) ($startColor['r'] + ($endColor['r'] - $startColor['r']) * $ratio);
            $g = (int) ($startColor['g'] + ($endColor['g'] - $startColor['g']) * $ratio);
            $b = (int) ($startColor['b'] + ($endColor['b'] - $startColor['b']) * $ratio);

            $color = imagecolorallocate($canvas, $r, $g, $b);

            if ($direction === 'vertical') {
                imageline($canvas, 0, $i, $width, $i, $color);
            } else {
                imageline($canvas, $i, 0, $i, $height, $color);
            }
        }
    }

    /**
     * Aplica una sombra
     */
    protected function applyShadow($canvas, int $x, int $y, int $width, int $height, array $shadow): void
    {
        $offsetX = $shadow['offset_x'] ?? 5;
        $offsetY = $shadow['offset_y'] ?? 5;
        $blur = $shadow['blur'] ?? 10;
        $color = $this->hexToRgb($shadow['color'] ?? '#000000');
        $opacity = $shadow['opacity'] ?? 50;

        $shadowColor = imagecolorallocatealpha(
            $canvas,
            $color['r'],
            $color['g'],
            $color['b'],
            127 - (int) ($opacity * 1.27)
        );

        imagefilledrectangle(
            $canvas,
            $x + $offsetX,
            $y + $offsetY,
            $x + $width + $offsetX,
            $y + $height + $offsetY,
            $shadowColor
        );
    }

    /**
     * Aplica bordes redondeados (simplificado)
     */
    protected function applyBorderRadius($canvas, int $x, int $y, int $width, int $height, int $radius): void
    {
        // Implementación simplificada - en producción usar librería de imágenes más avanzada
    }

    /**
     * Aplica efectos adicionales
     */
    protected function applyEffects($canvas, array $effects): void
    {
        if (isset($effects['brightness'])) {
            imagefilter($canvas, IMG_FILTER_BRIGHTNESS, $effects['brightness']);
        }

        if (isset($effects['contrast'])) {
            imagefilter($canvas, IMG_FILTER_CONTRAST, $effects['contrast']);
        }

        if (isset($effects['saturation'])) {
            // GD no tiene filtro de saturación nativo
        }
    }

    /**
     * Convierte color hexadecimal a RGB
     */
    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Escala la configuración para preview
     */
    protected function scaleConfig(array $config, float $scale): array
    {
        $scaled = [];

        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $scaled[$key] = $this->scaleConfig($value, $scale);
            } elseif (is_numeric($value) && in_array($key, ['x', 'y', 'width', 'height', 'size', 'max_width'])) {
                $scaled[$key] = (int) ($value * $scale);
            } else {
                $scaled[$key] = $value;
            }
        }

        return $scaled;
    }

    /**
     * Ajusta texto con word wrap
     */
    protected function wordWrapText(string $text, string $fontPath, int $fontSize, int $maxWidth): string
    {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = $currentLine . ($currentLine ? ' ' : '') . $word;
            $box = imagettfbbox($fontSize, 0, $fontPath, $testLine);
            $lineWidth = abs($box[2] - $box[0]);

            if ($lineWidth > $maxWidth && $currentLine) {
                $lines[] = $currentLine;
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }

        if ($currentLine) {
            $lines[] = $currentLine;
        }

        return implode("\n", $lines);
    }
}
