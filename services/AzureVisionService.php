<?php
class AzureVisionService {
    private $prediction_key;
    private $base_endpoint;

    public function __construct($prediction_key, $base_endpoint) {
        $this->prediction_key = $prediction_key;
        $this->base_endpoint = $base_endpoint;
    }

    /**
     * Analiza una imagen a partir de su URL.
     * @param string $imageUrl La URL de la imagen a analizar.
     * @return array|null Los resultados de la predicción o null si hay un error.
     */
    public function analyzeImageUrl($imageUrl) {
        $endpoint = $this->base_endpoint . "/url";
        $data = json_encode(['Url' => $imageUrl]);

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Prediction-Key: {$this->prediction_key}\r\n" .
                            "Content-Type: application/json\r\n",
                'content' => $data
            ]
        ];

        return $this->sendRequest($endpoint, $options);
    }

    /**
     * Analiza una imagen a partir de datos de archivo.
     * @param string $imageData El contenido binario de la imagen.
     * @return array|null Los resultados de la predicción o null si hay un error.
     */
    public function analyzeImageFile($imageData) {
        $endpoint = $this->base_endpoint . "/image";

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Prediction-Key: {$this->prediction_key}\r\n" .
                            "Content-Type: application/octet-stream\r\n",
                'content' => $imageData
            ]
        ];

        return $this->sendRequest($endpoint, $options);
    }

    /**
     * Envía la petición a la API de Azure.
     * @param string $endpoint El endpoint completo de la API.
     * @param array $options Las opciones para el stream context.
     * @return array|null Los resultados decodificados o null si hay un error.
     */
    private function sendRequest($endpoint, $options) {
        $context = stream_context_create($options);
        $result = @file_get_contents($endpoint, false, $context);

        if ($result === FALSE) {
            return null;
        }

        return json_decode($result, true);
    }
}
