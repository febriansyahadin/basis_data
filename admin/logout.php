<?php

session_start();

// Hapus semua data sesi
session_unset();
session_destroy();

// Arahkan ke halaman index yang berada di luar folder admin
header("Location: ../index.php");
exit();
?>