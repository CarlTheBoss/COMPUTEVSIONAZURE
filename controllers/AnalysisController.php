<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../services/AzureVisionService.php';

class AnalysisController {
    
    public function handleRequest() {
        $error = '';
        $predictions = null;
        $image_url_display = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $azureService = new AzureVisionService(AZURE_PREDICTION_KEY, AZURE_BASE_ENDPOINT);

            // Opción 1: Subida de archivo local
            if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
                $this->handleFileUpload($azureService, $predictions, $error, $image_url_display);
            }
            // Opción 2: Envío de URL
            else if (isset($_POST["image_url"]) && !empty($_POST["image_url"])) {
                $this->handleUrlUpload($azureService, $predictions, $error, $image_url_display);
            }
            // Opción 3: No se envió nada
            else {
                $error = "Por favor, sube un archivo o introduce una URL.";
            }
        }

        // Cargar la vista y pasarle los datos
        require_once __DIR__ . '/../views/analysis_view.php';
    }

    private function handleFileUpload($azureService, &$predictions, &$error, &$image_url_display) {
        if (!file_exists(UPLOADS_DIR)) {
            mkdir(UPLOADS_DIR, 0777, true);
        }

        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $target_file = UPLOADS_DIR . uniqid() . "." . $imageFileType;
        $allowed_types = ["jpg", "jpeg", "png", "gif"];

        if (!in_array($imageFileType, $allowed_types)) {
            $error = "Solo se permiten archivos JPG, JPEG, PNG y GIF.";
            return;
        }

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_data = file_get_contents($target_file);
            $image_url_display = $target_file;
            $predictions = $azureService->analyzeImageFile($image_data);
            if ($predictions === null) {
                $error = "❌ Error al conectar con el servicio de Azure.";
            }
        } else {
            $error = "Error al subir la imagen.";
        }
    }

    private function handleUrlUpload($azureService, &$predictions, &$error, &$image_url_display) {
        $image_url = filter_var($_POST["image_url"], FILTER_SANITIZE_URL);

        if (filter_var($image_url, FILTER_VALIDATE_URL)) {
            $image_url_display = $image_url;
            $predictions = $azureService->analyzeImageUrl($image_url);
            if ($predictions === null) {
                $error = "❌ Error al conectar con el servicio de Azure.";
            }
        } else {
            $error = "La URL de la imagen no es válida.";
        }
    }
}
