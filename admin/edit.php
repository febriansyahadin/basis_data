<?php
require '../news.php';

$db = getDB();
$collection = $db->posts;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    var_dump($_FILES); // Debugging untuk memeriksa file yang diunggah
    $id = new MongoDB\BSON\ObjectId($_POST['id']);
    
    // Data yang akan diperbarui
    $updateData = [
        'title' => $_POST['title'],
        'content' => $_POST['content'],
        'summary' => $_POST['summary'],
        'author' => $_POST['author'],
        'category' => $_POST['category'],
        'updated_at' => new MongoDB\BSON\UTCDateTime()
    ];

    // Jika file gambar diunggah
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $updateData['image'] = new MongoDB\BSON\Binary(
            file_get_contents($_FILES['image']['tmp_name']),
            MongoDB\BSON\Binary::TYPE_GENERIC
        );
    } else {
        // Jika tidak ada file yang diunggah, gunakan gambar lama
        $existingPost = $collection->findOne(['_id' => $id]);
        if (isset($existingPost['image'])) {
            $updateData['image'] = $existingPost['image'];
        }
    }

    // Perbarui dokumen di database
    $collection->updateOne(
        ['_id' => $id],
        ['$set' => $updateData]
    );

    // Redirect ke halaman admin
    header("Location: adminpanel.php");
    exit();
} else {
    $id = new MongoDB\BSON\ObjectId($_GET['id']);
    $posts = $collection->findOne(['_id' => $id]);
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Postingan Berita</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Poppins:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        header {
            background: linear-gradient(90deg, #0056b3, #77dd77); /* Gradasi biru tua ke hijau pastel */
            color: white;
            padding: 15px;
            font-weight: bold;
            font-size: 24px;
            margin-bottom: 50px;
        }

        h2{
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }
        .form-container {
            margin-top: 20px;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 50px;
        }
        
    </style>
</head>
<body>

<header class="d-flex justify-content-between align-items-center">
    <h2 class="d-flex align-items-center">
        <img src="../img/News.png" alt="Logo" style="height: 60px; object-fit: contain; margin-right: 10px;">
        NEWS.ID
    </h2>
</header>

<div class="container mt-4">
    <div class="form-container">
        <form method="POST" enctype="multipart/form-data">
            <h2 class="mb-4">Input Berita</h2>
            <input type="hidden" name="id" value="<?php echo $posts['_id']; ?>">
            <div class="mb-3">
                <label for="title" class="form-label">Judul *</label>
                <input type="text" class="form-control" id="title" name="title" placeholder="Masukkan Judul Berita" value="<?php echo $posts['title']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Konten *</label>
                <textarea class="form-control" id="content" name="content" rows="3" placeholder="Isi Konten Berita" required><?php echo $posts['content']; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="summary" class="form-label">Ringkasan *</label>
                <input type="text" class="form-control" id="summary" name="summary" placeholder="Ringkasan Berita" value="<?php echo $posts['summary']; ?>"required>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="category" class="form-label">Kategori *</label>
                    <select id="category" name="category" class="form-select" required>
                        <option value="">Pilih Kategori</option>
                        <option value="Politik" <?php echo $posts['category'] == 'Politik' ? 'selected' : '' ?>>Politik</option>
                        <option value="Olahraga" <?php echo $posts['category'] == 'Olahraga' ? 'selected' : '' ?>>Olahraga</option>
                        <option value="Ekonomi" <?php echo $posts['category'] == 'Ekonomi' ? 'selected' : '' ?>>Ekonomi</option>
                        <option value="Teknologi" <?php echo $posts['category'] == 'Teknologi' ? 'selected' : '' ?>>Teknologi</option>
                        <option value="Kesehatan" <?php echo $posts['category'] == 'Kesehatan' ? 'selected' : '' ?>>Kesehatan</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="author" class="form-label">Penulis *</label>
                    <input type="text" id="author" name="author" class="form-control" placeholder="Nama Penulis" value="<?php echo $posts['author'] ?>"required>
                </div>
            </div>
            <div class="mb-3 mt-3">
                <label for="image" class="form-label">Image *</label>
                 <!-- Tampilkan gambar jika sudah ada -->
                <?php if (!empty($posts['image'])): ?>
                    <div class="mb-2">
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($posts['image']->getData()); ?>" 
                            alt="Preview Gambar" style="max-height: 150px;">
                    </div>
                <?php endif; ?>
                
                <input type="file" class="form-control" id="image" name="image" accept=".jpg,.jpeg,.png" >
            </div>
            <div class="d-flex justify">
                <a href="adminpanel.php" class="btn btn-secondary">Batal Edit</a>
                <div class="ms-2">
                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>