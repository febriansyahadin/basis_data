<?php
require 'news.php';

$client = new MongoDB\Client("mongodb://localhost:27017");
$db = $client->Berita;
$collection = $db->posts;

// Ambil kategori dari URL
$category = isset($_GET['category']) ? $_GET['category'] : null;

// Membuat query pencarian untuk memfilter data MongoDB berdasarkan kategori
$searchQuery = [];
if ($category) {
    $searchQuery = [
        'category' => ['$regex' => '^' . preg_quote($category) . '$', '$options' => 'i']
    ];
}

// Ambil data berita berdasarkan kategori
$newsList = $collection->find($searchQuery, ['sort' => ['created_at' => -1]]);

// Ambil 5 berita terbaru untuk sidebar
$latestNews = $collection->find([], ['sort' => ['created_at' => -1], 'limit' => 5]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEWS.ID</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Poppins:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(180deg, #ffffff, #e9ecef);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        header {
            background: linear-gradient(90deg, #0056b3, #77dd77);
            color: white;
            padding: 5px 0 !important; /* Kurangi padding atas dan bawah */
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        .navbar {
            margin-top: -10px !important; /* Naikkan navbar */
            padding: 0 !important; /* Hapus padding yang tidak diperlukan */
            height: 35px !important; /* Atur tinggi navbar */
        }

        .nav-link {
            color: white;
            font-weight: bold;
            padding: 3px 8px !important; /* Kurangi padding */
            font-size: 16px !important; /* Sesuaikan font */
        }

        .nav-link.active, .nav-link:hover {
            color: #f8f9fa;
            text-decoration: underline;
        }

        .search-bar input {
            height: 30px; /* Menyesuaikan tinggi input agar tidak terlalu besar */
            font-size: 14px; /* Menyesuaikan ukuran font input */
        }

        .search-bar a {
            font-size: 14px; /* Menyesuaikan ukuran font button agar lebih kecil */
            padding: 5px 10px; /* Mengurangi padding button */
        }

        .header-container {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 5px 0 !important; /* Kurangi padding */
        
        }
        .header-container h2 {
            margin: 0 !important;
            gap: 5px !important;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }

        .header-container img {
            margin-right: 0 !important;
    
        }

        .search-bar {
            display: flex;
            align-items: center;
        }

        .search-bar input {
            border: 2px solid #00c853;
            border-radius: 25px;
            padding: 10px 15px;
            margin-right: 10px;
        }

        .btn-custom {
            background-color: #007bff;
            border: none;
            color: white;
            border-radius: 25px;
            padding: 10px 15px;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }
        .nav-link {
            color: white;
            font-weight: bold;
        }

        .nav-link.active, .nav-link:hover {
            color: #f8f9fa;
            text-decoration: underline;
        }


        .carousel-inner img {
            width: 100%;
            height: 500px; /* Menyesuaikan tinggi gambar */
            object-fit: contain; /* Menjaga gambar agar tetap terproyeksi sepenuhnya tanpa terpotong */
            
        }

        .card {
            background-color: #ffffff;
            border: none;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-title a {
            color: #333;
            font-weight: bold;
        }

        .card-title a:hover {
            color: #007bff;
            text-decoration: underline;
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        .card-body {
            padding: 15px;
        }

        footer {
            background: linear-gradient(90deg, #0056b3, #77dd77);
            color: white;
            padding: 15px 0;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        footer p {
            margin: 0;
            font-size: 16px;
            text-align: center;
        }

        footer a {
            color: #f8f9fa;
            text-decoration: underline;
        }

        footer a:hover {
            color: #00c853;
        }

        .sidebar {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        .sidebar h5 {
            font-weight: bold;
            color: #0056b3;
        }

        .sidebar ul {
            padding-left: 0;
        }

        .sidebar ul li {
            margin-bottom: 10px;
        }

        .hidden {
            display: none;
        }

        .carousel-item {
            transition: transform 1s ease, opacity 1s ease;
        }

        .carousel-inner {
            position: relative;
        }

        .carousel-image {
            height: 500px; 
            object-fit: cover;
        }

        .custom-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(70, 70, 70, 0.6);
            color: #fff;
            padding: 15px; 
            text-align: center;
            box-sizing: border-box;
        }

        .custom-caption h5 {
            font-size: 15px;
            font-weight: bold;
            margin: 0;
        }

        .custom-caption p {
            font-size: 14px;
            margin: 5px 0 0;
        }
        
    </style>
</head>
<body>
<header>
    <div class="container header-container">
        <h2 class=" d-flex align-items-center">
            <img src="img/News.png" alt="Logo" style="height: 60px; object-fit: contain;"> NEWS.ID
        </h2>
        <div class="search-bar">
            <input type="text" id="searchInput" class="form-control" placeholder="Cari berita">
            <a href="admin/login.php" class="btn btn-custom">Login</a>
        </div>
    </div>
    <nav class="container mt-3">
    <ul class="nav justify-content-center navbar">
        <li class="nav-item"><a class="nav-link <?php echo empty($category) ? 'active' : ''; ?>" href="index.php">Semua Berita</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $category == 'Politik' ? 'active' : ''; ?>" href="index.php?category=Politik">Politik</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $category == 'Olahraga' ? 'active' : ''; ?>" href="index.php?category=Olahraga">Olahraga</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $category == 'Ekonomi' ? 'active' : ''; ?>" href="index.php?category=Ekonomi">Ekonomi</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $category == 'Teknologi' ? 'active' : ''; ?>" href="index.php?category=Teknologi">Teknologi</a></li>
        <li class="nav-item"><a class="nav-link <?php echo $category == 'Kesehatan' ? 'active' : ''; ?>" href="index.php?category=Kesehatan">Kesehatan</a></li>
    </ul>
</nav>

</header>

<main class="container my-4">
    <div class="row">
        <div id="newsCarousel" class="carousel slide mb-4 col-12">
            <div class="carousel-inner">
            <?php
                $carouselNews = $category ? 
                    $collection->find(['category' => ['$regex' => '^' . preg_quote($category) . '$', '$options' => 'i']], ['sort' => ['created_at' => -1], 'limit' => 3]) :
                    $collection->find([], ['sort' => ['created_at' => -1], 'limit' => 3]);

                $isFirst = true;
                foreach ($carouselNews as $news): 
                ?>
                    <div class="carousel-item <?php echo $isFirst ? 'active' : ''; ?>">
                        <a href="detail.php?berita=<?php echo $news['_id']; ?>" class="d-block w-100">
                            <img src="<?php echo isset($news['image']) ? 'data:image/jpeg;base64,' . base64_encode($news['image']->getData()) : 'default.jpg'; ?>" 
                                class="d-block w-100 carousel-image" alt="Gambar Berita">
                            <div class="carousel-caption custom-caption">
                                <h5><?php echo htmlspecialchars($news['title']); ?></h5>
                                <p><?php echo htmlspecialchars(substr($news['summary'], 0, 100)) . '...'; ?></p>
                            </div>
                        </a>
                    </div>
                <?php 
                    $isFirst = false;
                endforeach;
                ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#newsCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#newsCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>

        <!-- Berita Utama -->
        <div class="col-md-8">  
            <div class="row">
                <?php foreach ($newsList as $news): 
                ?>
                    <div class="col-md-4 mb-4 searchable-card">
                        <div class="card">
                            <img src="<?php echo isset($news['image']) ? 'data:image/jpeg;base64,' . base64_encode($news['image']->getData()) : 'default.jpg'; ?>" class="card-img-top" alt="Gambar Berita">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="detail.php?berita=<?php echo $news['_id']; ?>" class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($news['title']); ?>
                                    </a>
                                </h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($news['summary'], 0, 100)) . '...'; ?></p>
                                <p class="text-muted small"><?php echo date('d-m-Y H:i:s', $news['created_at']->toDateTime()->getTimestamp()); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <div class="sidebar">
                <h5 class="mb-3">Kategori Populer</h5>
                <ul class="list-group">
                    <li class="list-group-item"><a href="?category=Politik">Politik</a></li>
                    <li class="list-group-item"><a href="?category=Olahraga">Olahraga</a></li>
                    <li class="list-group-item"><a href="?category=Ekonomi">Ekonomi</a></li>
                </ul>
                <h5 class="mt-4">Berita Terbaru</h5>
                <?php foreach ($latestNews as $latest): ?>
                    <div class="mb-2">
                        <a href="detail.php?berita=<?php echo $latest['_id']; ?>" class="text-decoration-none">
                            <?php echo htmlspecialchars($latest['title']); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<script>
    const searchInput = document.getElementById("searchInput");
    const cards = document.querySelectorAll(".searchable-card");

    searchInput.addEventListener("input", function () {
        const keyword = searchInput.value.toLowerCase();
        cards.forEach(card => {
            const title = card.querySelector(".card-title").textContent.toLowerCase();
            const text = card.querySelector(".card-text").textContent.toLowerCase();
            // Sembunyikan jika tidak mengandung kata kunci
            card.closest(".col-md-4").classList.toggle("hidden", !(title.includes(keyword) || text.includes(keyword)));
        });
    });
</script>

<footer>
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> NEWS.ID | Semua Hak Dilindungi</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
