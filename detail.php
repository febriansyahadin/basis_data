<?php
require 'news.php';

$client = new MongoDB\Client("mongodb://localhost:27017");
$collection = $client->Berita->posts;
$db = $client->Berita;
$commentsCollection = $db->comments;
$ratingCollection = $db->rating;

// Ambil ID berita dari parameter query string
$beritaId = $_GET['berita'] ?? null;

//mengambil detail spesifik dari sebuah dokumen berdasarkan id
$news = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($beritaId)]);

// Ambil komentar untuk berita
// Ambil komentar untuk berita
$comments = [];
if ($beritaId) {
    // Ambil semua komentar terkait berita, urutkan berdasarkan waktu pembuatan (terlama di atas)
    $cursor = $commentsCollection->find(['news_id' => $beritaId], ['sort' => ['created_at' => 1]]);
    foreach ($cursor as $comment) {
        $comments[(string)$comment['_id']] = $comment; // Simpan dengan ID sebagai kunci untuk akses mudah
    }

    // Fungsi untuk membangun hierarki komentar
    function buildCommentTree($comments, $parentId = null) {
        $tree = [];
        foreach ($comments as $comment) {
            // Jika parent_id cocok, tambahkan sebagai balasan
            if (($comment['parent_id'] ?? null) == $parentId) {
                $comment['replies'] = buildCommentTree($comments, (string)$comment['_id']);
                $tree[] = $comment;
            }
        }
        return $tree;
    }

    // Bangun pohon komentar (hierarki)
    $commentTree = buildCommentTree($comments);
}

// Hitung rata-rata rating
$rating = $ratingCollection->find(['news_id' => $beritaId]);
$averageRating = 0;
$ratingArray = iterator_to_array($rating);
if (count($ratingArray) > 0) {
    $averageRating = array_sum(array_column($ratingArray, 'rating')) / count($ratingArray); 
}
// echo json_encode($ratingArray);

$ratedIp = $ratingCollection->find(['ip' => getenv('REMOTE_ADDR'), 'news_id' => $beritaId]);
$rated = count(iterator_to_array($ratedIp));

// Tangani form komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['rating']) && $rated == 0) {
    $rating = $_POST['rating'] ?? 0;
    $ratingCollection->insertOne([
      'news_id' => $beritaId,
      'rating' => $rating,
      'ip' => getenv('REMOTE_ADDR'),
      'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    header("Location: detail.php?berita=" . $beritaId); // Refresh halaman
    exit;
  } else {
    $name = !empty($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : 'Anonim'; // Default ke "Anonim"
    $comment = !empty($_POST['comment']) ? htmlspecialchars(trim($_POST['comment'])) : '';
    $parentId = $_POST['parent_id'] ?? null;

    if (!empty($comment)) {
        $commentsCollection->insertOne([
            'news_id' => $beritaId,
            'name' => $name,
            'comment' => $comment,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'parent_id' => $parentId ? new MongoDB\BSON\ObjectId($parentId) : null,
        ]);
        header("Location: detail.php?berita=" . $beritaId);
        exit;
    } else {
        $errorMessage = 'Komentar tidak boleh kosong!';
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Berita</title>
  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Poppins:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@coreui/coreui@5.2.0/dist/css/themes/bootstrap/bootstrap.min.css" rel="stylesheet" integrity="sha384-nQQlHXZO4YmST3YDqk/JU3f1t2D58a/nPd1QbiLecouKn68glzRym4BlOxlr5Rrg" crossorigin="anonymous">
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <link href="star-rating.min.css" rel="stylesheet">
  <style>
    body {
        background-color: #f9f9f9; /* Warna background netral */
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
    }

    header {
      background: linear-gradient(90deg, #0056b3, #77dd77); /* Gradasi biru tua ke hijau pastel */
        color: white;
        padding: 10px 0;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    }

    h2{
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            margin-left: 30px;
        }

    footer {
      background: linear-gradient(90deg, #0056b3, #77dd77); /* Gradasi biru tua ke hijau pastel */
        color: white;
        text-align: center;
        padding: 20px 0;
        box-shadow: 0px -4px 8px rgba(0, 0, 0, 0.2);
    }

    footer p {
        margin: 0;
        font-size: 16px; /* Ukuran font untuk footer */
    }

    footer a {
        color: #f8f9fa;
        text-decoration: underline;
    }

    footer a:hover {
        color: #00c853; /* Efek hover untuk link footer */
    }

    h1 {
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    color: #333;
    margin-bottom: 20px;
  }

  </style>
</head>
<body>
<header class="sticky-top">
  <div class="d-flex align-items-center justify-content-between">
    <!-- Logo dan teks NEWS.ID -->
    <h2 class="d-flex align-items-center">
      <img src="img/News.png" alt="Logo" style="height: 60px; object-fit: contain; margin-right: 10px;">
      NEWS.ID
    </h2>

    <!-- Dropdown kategori, di sebelah kanan -->
    <div class="dropdown ms-auto">
      <button class="btn btn-link text-white dropdown-toggle text-decoration-none" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
        Kategori
      </button>
      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        <li><a class="dropdown-item" href="index.php?category=Politik">Politik</a></li>
        <li><a class="dropdown-item" href="index.php?category=Olahraga">Olahraga</a></li>
        <li><a class="dropdown-item" href="index.php?category=Ekonomi">Ekonomi</a></li>
        <li><a class="dropdown-item" href="index.php?category=Teknologi">Teknologi</a></li>
        <li><a class="dropdown-item" href="index.php?category=Kesehatan">Kesehatan</a></li>
      </ul>
    </div>
    <a href="index.php" class="text-white text-decoration-none me-5 ms-3"> Beranda</a>
  </div>
</header>

  <main class="container my-4">
    <?php if ($news): ?>    
      <h1 class="mb-3"><?php echo htmlspecialchars($news['title']); ?></h1>
      <img src="<?php
      if (isset($news['image']) && $news['image'] instanceof MongoDB\BSON\Binary) {
          echo 'data:image/jpeg;base64,' . base64_encode($news['image']->getData());
      } else {
          echo 'default.jpg'; // Fallback image if binary data is not found
      }
      ?>" class="img-fluid mb-4" alt="Gambar Berita">
      <p class="text-muted mt-2"><?php echo date('d-m-Y H:i:s', $news['created_at']->toDateTime()->getTimestamp()); ?></p>

      <div><?php echo $news['content']; ?></div>

      <!-- Form Komentar -->
     <section class="mt-5">
        <h3>Tulis Komentar</h3>
        <form name="commentForm" method="POST" class="mb-4">
          <div class="mb-3">
            <label for="name" class="form-label">Nama</label>
            <input type="text" id="name" name="name" class="form-control" placeholder="Nama Anda (Opsional)">
          </div>
          <div class="mb-3">
            <label for="comment" class="form-label">Komentar</label>
            <textarea id="comment" name="comment" rows="2" class="form-control" placeholder="Tuliskan komentar Anda di sini..." required></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Kirim Komentar</button>
        </form>
      </section>

      <!-- Daftar Komentar -->
      <section>
        <h3>Komentar</h3>
        <?php if (!empty($comments)): ?>
          <ul class="list-group">
          <?php function renderComments($comments) { ?>
                <?php foreach ($comments as $comment): ?>
                    <li class="list-group-item">
                        <strong><?php echo htmlspecialchars($comment['name'] ?? 'Anonim'); ?></strong> 
                        <small class="text-muted"><?php echo date('d-m-Y H:i:s', $comment['created_at']->toDateTime()->getTimestamp()); ?></small>
                        <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                        <button class="btn btn-sm btn-link reply-btn" data-parent-id="<?php echo $comment['_id']; ?>">Balas</button>
                        <!-- Form Balasan -->
                        <form method="POST" class="reply-form mt-2" style="display: none;">
                            <input type="hidden" name="parent_id" value="<?php echo $comment['_id']; ?>">
                            <div class="mb-3">
                                <label for="reply-name-<?php echo $comment['_id']; ?>" class="form-label">Nama</label>
                                <input type="text" id="reply-name-<?php echo $comment['_id']; ?>" name="name" class="form-control" placeholder="Nama Anda (Opsional)">
                            </div>
                            <div class="mb-3">
                                <textarea name="comment" class="form-control" placeholder="Tuliskan balasan Anda..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Kirim Balasan</button>
                        </form>
                        <!-- Rekursif tampilkan balasan -->
                        <?php if (!empty($comment['replies'])): ?>
                            <ul class="list-group mt-3">
                                <?php renderComments($comment['replies']); ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            <?php } ?>
            <?php renderComments($commentTree); ?>
        </ul>
    <?php else: ?>
        <p class="text-muted">Belum ada komentar.</p>
    <?php endif; ?>
      </section>

      <section>
        <h3>Rating</h3>
        <div class="d-flex align-items-center mb-2  ">Rata-rata rating: <?= number_format($averageRating, 1) ?></div>
        <!-- masukkan rating -->
        <form name="ratingForm" method="POST" class="mb-4">
            <select class="star-rating" name="rating" data-options='{"clearable":false, "tooltip":false}'>
              <option value="5"></option>
              <option value="4"></option>
              <option value="3"></option>
              <option value="2"></option>
              <option value="1"></option>
            </select>
          <?php if ($rated == 0): ?>
            <button type="submit" class="btn btn-primary mt-1">Kirim Rating</button>
          <?php else: ?>
            <button type="submit" class="btn btn-primary mt-1" disabled>Rating Sudah Diberikan</button>
          <?php endif; ?>
        </form>
      </section>
    <?php else: ?>
      <h1>Berita tidak ditemukan</h1>
    <?php endif; ?>
    <a href="index.php" class="btn btn-secondary mt-4">Kembali ke Beranda</a>
  </main>

  <footer>
    <div class="container">
      <p>&copy; <?php echo date('Y'); ?> NEWS.ID | Semua Hak Dilindungi</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="star-rating.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var stars = new StarRating('.star-rating');
      stars.rebuild();
    });
  </script>
  <script>
    document.querySelectorAll('.reply-btn').forEach(button => {
        button.addEventListener('click', () => {
            const replyForm = button.nextElementSibling;
            replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
        });
    });
</script>
</body>
</html>
