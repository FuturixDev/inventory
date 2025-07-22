<?php $base = "/"; ?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 shadow-sm">
  <a class="navbar-brand" href="<?= $base ?>dashboard.php">📊 管理系統</a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav ms-auto">
      <li class="nav-item"><a class="nav-link" href="<?= $base ?>Product/products.php">📦 產品列表</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= $base ?>Product/inventory_log.php">📥 登錄進貨</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= $base ?>Product/inventory_history.php">📜 庫存紀錄</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= $base ?>Sales/sales.php">💰 銷售紀錄</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= $base ?>Sales/add_sale.php">📝 登錄銷售</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= $base ?>KOL/kols.php">🎁 KOL</a></li>    
      <li class="nav-item"><a class="nav-link" href="<?= $base ?>expenses.php">💸 營運支出</a></li>      
      <li class="nav-item"><a class="nav-link" href="<?= $base ?>pnl.php">📄 損益報表</a></li>
    </ul>
  </div>
</nav>
