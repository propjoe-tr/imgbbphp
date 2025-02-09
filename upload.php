<?php
require_once 'config.php';

class ImageUploader {
    private $errors = [];
    private $responses = [];

    public function handleUpload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
            $files = $this->reArrayFiles($_FILES['images']);
            
            foreach ($files as $file) {
                if ($this->validateImage($file)) {
                    $uploadResult = $this->uploadToImgBB($file);
                    if ($uploadResult) {
                        $this->responses[] = $uploadResult;
                    }
                }
            }
            
            return !empty($this->responses);
        }
        return false;
    }

    private function reArrayFiles($files) {
        $fileArray = [];
        $fileCount = count($files['name']);
        $fileKeys = array_keys($files);

        for ($i = 0; $i < $fileCount; $i++) {
            foreach ($fileKeys as $key) {
                $fileArray[$i][$key] = $files[$key][$i];
            }
        }

        return $fileArray;
    }

    private function validateImage($image) {
        if (!in_array($image['type'], ALLOWED_TYPES)) {
            $this->errors[] = "'{$image['name']}' - Sadece JPEG, PNG ve GIF dosyaları yüklenebilir.";
            return false;
        }
        
        if ($image['size'] > MAX_FILE_SIZE) {
            $this->errors[] = "'{$image['name']}' - Dosya boyutu 5MB'dan küçük olmalıdır.";
            return false;
        }
        
        return true;
    }

    private function uploadToImgBB($image) {
        $image_base64 = base64_encode(file_get_contents($image['tmp_name']));
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.imgbb.com/1/upload',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'key' => IMGBB_API_KEY,
                'image' => $image_base64,
                'name' => $image['name']
            ],
            CURLOPT_RETURNTRANSFER => true
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        return $result['success'] ? $result['data'] : false;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getResponses() {
        return $this->responses;
    }
}

$uploader = new ImageUploader();
$uploadSuccess = $uploader->handleUpload();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="upload-container">
            <div class="text-center mb-4">
                <h1 class="display-4"><?php echo SITE_NAME; ?></h1>
                <p class="lead text-muted"><?php echo SITE_DESCRIPTION; ?></p>
            </div>
            
            <?php if (!empty($uploader->getErrors())): ?>
                <?php foreach ($uploader->getErrors() as $error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($uploadSuccess): ?>
                <div class="upload-success">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Resimler başarıyla yüklendi!
                    </div>
                    <div class="result-container">
                        <?php foreach ($uploader->getResponses() as $index => $response): ?>
                            <div class="image-result mb-4">
                                <h5 class="text-muted"><?php echo $response['title']; ?></h5>
                                <div class="text-center mb-3">
                                    <img src="<?php echo $response['url']; ?>" 
                                         class="preview-image" 
                                         style="display: block;"
                                         alt="<?php echo $response['title']; ?>">
                                </div>
                                <div class="url-container p-3 bg-light rounded mb-3">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-link"></i></span>
                                        <input type="text" 
                                               class="form-control" 
                                               value="<?php echo $response['url']; ?>" 
                                               id="url-<?php echo $index; ?>"
                                               readonly>
                                        <button class="btn btn-outline-primary copy-btn" 
                                                data-clipboard-target="#url-<?php echo $index; ?>">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <a href="<?php echo $response['delete_url']; ?>" 
                                       class="btn btn-outline-danger btn-sm" 
                                       target="_blank">
                                        <i class="fas fa-trash"></i> Bu Resmi Sil
                                    </a>
                                </div>
                            </div>
                            <hr class="my-4">
                        <?php endforeach; ?>
                        <div class="d-grid">
                            <button class="btn btn-primary" onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>'">
                                <i class="fas fa-upload"></i> Yeni Resimler Yükle
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="upload-form">
                    <form method="POST" enctype="multipart/form-data" id="upload-form">
                        <div class="drag-area" id="drag-area">
                            <div class="icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <h4>Sürükle & Bırak veya Tıkla</h4>
                            <p class="text-muted">Desteklenen formatlar: JPEG, PNG, GIF</p>
                            <small class="text-muted">Maksimum dosya boyutu: 5MB</small>
                            <input type="file" name="images[]" id="file-input" hidden accept="image/*" multiple>
                        </div>
                        <div id="preview-container" class="row g-3 mt-3"></div>
                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary upload-btn" id="upload-btn" disabled>
                                <i class="fas fa-upload"></i> Yükle
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="text-center text-muted py-3">
        <small>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?></small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>
    <script src="script.js"></script>
</body>
</html> 