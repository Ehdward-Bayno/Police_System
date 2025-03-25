<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>National Police Commission - Criminal Profiling System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
    <?php if (isLoggedIn() && basename($_SERVER['PHP_SELF']) !== 'login.php' && basename($_SERVER['PHP_SELF']) !== 'register.php'): ?>
    <header class="bg-primary text-white">
        <div class="container py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="bi bi-shield me-2 fs-4"></i>
                    <div>
                        <h1 class="fs-4 mb-0 fw-bold">National Police Commission</h1>
                        <p class="small mb-0">Criminal Profiling System</p>
                    </div>
                </div>
                
                <nav class="d-none d-md-flex">
                    <ul class="nav">
                        <li class="nav-item">
                            <a href="/dashboard.php" class="nav-link text-white">Home</a>
                        </li>
                        <li class="nav-item">
                            <a href="/search.php" class="nav-link text-white">Search</a>
                        </li>
                        <li class="nav-item">
                            <a href="/upload.php" class="nav-link text-white">Upload</a>
                        </li>
                    </ul>
                </nav>
                
                <a href="/logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </a>
            </div>
        </div>
    </header>
    <?php endif; ?>
    
    <main class="bg-light min-vh-100">

