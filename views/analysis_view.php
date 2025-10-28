<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detector de Frutas</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f2f2f2; max-width: 800px; margin: 0 auto; padding: 20px; }
        .container { background: #fff; border-radius: 10px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; }
        .upload-form { text-align: center; margin-bottom: 20px; }
        .input-container { margin-bottom: 15px; }
        input[type="url"], input[type="file"] { width: 80%; padding: 8px; margin-bottom: 10px; border-radius: 5px; border: 1px solid #ccc; }
        button { background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #45a049; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; text-align: center; }
        img.preview { display: block; margin: 20px auto; max-width: 300px; border-radius: 8px; }
        .results {
            margin-top: 30px;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #eee;
            text-align: center;
        }
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            display: inline-block;
            min-width: 250px;
        }
        .metric-card h2 {
            margin-top: 0;
            font-size: 1.2em;
            font-weight: normal;
            opacity: 0.8;
        }
        .metric-card .prediction-name {
            font-size: 2.5em;
            font-weight: bold;
            margin: 10px 0;
        }
        .metric-card .prediction-probability {
            font-size: 1.5em;
            font-weight: bold;
            opacity: 0.9;
        }
        .tabs { margin-bottom: 15px; }
        .tab-button { background: #ddd; border: none; padding: 10px 15px; cursor: pointer; border-radius: 5px 5px 0 0; }
        .tab-button.active { background: #4CAF50; color: white; }
    </style>
</head>
<body>
<div class="container">
    <h1>Detector de Frutas 🍎</h1>

    <div class="upload-form">
        <div class="tabs">
            <button class="tab-button active" onclick="switchTab('url')">Usar URL</button>
            <button class="tab-button" onclick="switchTab('file')">Subir Archivo</button>
        </div>
        <form action="index.php" method="post" enctype="multipart/form-data">
            <div id="url-input" class="input-container">
                <input type="url" name="image_url" placeholder="Pega la URL de una imagen aquí" oninput="previewImageUrl(this.value)">
            </div>
            <div id="file-input" class="input-container" style="display: none;">
                <input type="file" name="image" accept="image/*" onchange="previewImageFile(event)">
            </div>
            <button type="submit">Analizar Imagen</button>
        </form>
    </div>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <img id="preview" class="preview" src="<?= htmlspecialchars($image_url_display) ?>" style="<?= $image_url_display ? '' : 'display: none;' ?>">

    <?php
    if ($predictions && isset($predictions['predictions']) && count($predictions['predictions']) > 0) {
        // Encontrar la predicción con la probabilidad más alta
        $best_prediction = array_reduce($predictions['predictions'], function($carry, $item) {
            return ($carry === null || $item['probability'] > $carry['probability']) ? $item : $carry;
        });
    ?>
        <div class="results">
            <div class="metric-card">
                <h2>Predicción Principal</h2>
                <div class="prediction-name"><?= htmlspecialchars($best_prediction['tagName']) ?></div>
                <div class="prediction-probability"><?= number_format($best_prediction['probability'] * 100, 2) ?>%</div>
            </div>
        </div>
    <?php
    } elseif ($predictions) { // Si hay respuesta pero no predicciones
    ?>
        <div class="results">
            <p>No se pudo identificar una fruta en la imagen.</p>
        </div>
    <?php
    }
    ?>
</div>

<script>
    const urlInputDiv = document.getElementById('url-input');
    const fileInputDiv = document.getElementById('file-input');
    const urlInput = urlInputDiv.querySelector('input');
    const fileInput = fileInputDiv.querySelector('input');
    const preview = document.getElementById('preview');

    function switchTab(tab) {
        document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
        document.querySelector(`.tab-button[onclick="switchTab('${tab}')"]`).classList.add('active');
        
        if (tab === 'url') {
            urlInputDiv.style.display = 'block';
            fileInputDiv.style.display = 'none';
            fileInput.value = ''; // Limpiar el input de archivo
        } else {
            urlInputDiv.style.display = 'none';
            fileInputDiv.style.display = 'block';
            urlInput.value = ''; // Limpiar el input de url
        }
        preview.style.display = 'none'; // Ocultar preview al cambiar
    }

    function previewImageUrl(url) {
        if (url) {
            preview.src = url;
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    }

    function previewImageFile(event) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        if (event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>
</body>
</html>
