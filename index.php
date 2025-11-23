<?php
// Konfigurasi
$excel_file = 'data_keuangan.csv';

// Fungsi untuk export ke Excel dengan format yang benar
function exportToExcel($file) {
    if (!file_exists($file) || filesize($file) == 0) {
        return;
    }
    
    // Baca data
    $all_data = readData($file);
    
    // Hitung total
    $total_pemasukan = 0;
    $total_pengeluaran = 0;
    foreach ($all_data as $row) {
        $jumlah = is_numeric($row['jumlah']) ? floatval($row['jumlah']) : 0;
        if ($row['jenis'] == 'Pemasukan') {
            $total_pemasukan += $jumlah;
        } else {
            $total_pengeluaran += $jumlah;
        }
    }
    $saldo = $total_pemasukan - $total_pengeluaran;
    
    // Set header untuk download Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Laporan_Keuangan_' . date('Y-m-d_His') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Mulai output HTML untuk Excel
    ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Calibri, Arial; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 8px; }
        th { background-color: #4472C4; color: white; font-weight: bold; text-align: center; }
        .header-title { background-color: #5B9BD5; color: white; font-weight: bold; font-size: 18px; text-align: center; padding: 12px; }
        .header-date { text-align: center; font-size: 12px; padding: 5px; }
        .section-title { background-color: #D9E1F2; font-weight: bold; padding: 8px; }
        .summary-label { background-color: #E7E6E6; font-weight: bold; padding: 8px; }
        .income { background-color: #C6EFCE; color: #006100; font-weight: bold; }
        .expense { background-color: #FFC7CE; color: #9C0006; font-weight: bold; }
        .balance { background-color: #FFEB9C; color: #9C5700; font-weight: bold; }
        .number { text-align: right; }
        .center { text-align: center; }
        .total-row { background-color: #D9E1F2; font-weight: bold; }
    </style>
</head>
<body>
    <table>
        <!-- Header -->
        <tr>
            <td colspan="7" class="header-title">LAPORAN KEUANGAN</td>
        </tr>
        <tr>
            <td colspan="7" class="header-date">Tanggal Cetak: <?php echo date('d/m/Y H:i:s'); ?></td>
        </tr>
        <tr>
            <td colspan="7">&nbsp;</td>
        </tr>
        
        <!-- Ringkasan -->
        <tr>
            <td colspan="7" class="section-title">RINGKASAN</td>
        </tr>
        <tr>
            <td colspan="3" class="summary-label">Total Pemasukan</td>
            <td colspan="4" class="income number">Rp <?php echo number_format($total_pemasukan, 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td colspan="3" class="summary-label">Total Pengeluaran</td>
            <td colspan="4" class="expense number">Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td colspan="3" class="summary-label">Saldo</td>
            <td colspan="4" class="balance number">Rp <?php echo number_format($saldo, 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td colspan="7">&nbsp;</td>
        </tr>
        
        <!-- Detail Transaksi -->
        <tr>
            <td colspan="7" class="section-title">DETAIL TRANSAKSI</td>
        </tr>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Nama</th>
            <th>Jenis</th>
            <th>Kategori</th>
            <th>Jumlah</th>
            <th>Keterangan</th>
        </tr>
        
        <?php 
        $no = 1;
        foreach ($all_data as $row): 
            $jenis_class = $row['jenis'] == 'Pemasukan' ? 'income' : 'expense';
        ?>
        <tr>
            <td class="center"><?php echo $no++; ?></td>
            <td class="center"><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
            <td><?php echo htmlspecialchars($row['nama']); ?></td>
            <td class="<?php echo $jenis_class; ?> center"><?php echo htmlspecialchars($row['jenis']); ?></td>
            <td><?php echo htmlspecialchars($row['kategori']); ?></td>
            <td class="number">Rp <?php echo number_format(is_numeric($row['jumlah']) ? $row['jumlah'] : 0, 0, ',', '.'); ?></td>
            <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
        </tr>
        <?php endforeach; ?>
        
        <!-- Total -->
        <tr class="total-row">
            <td colspan="5" style="text-align: right; padding-right: 10px;">TOTAL</td>
            <td class="number">Rp <?php echo number_format($saldo, 0, ',', '.'); ?></td>
            <td>&nbsp;</td>
        </tr>
    </table>
</body>
</html>
    <?php
    exit;
}

// Proses download Excel
if (isset($_GET['download_excel'])) {
    exportToExcel($excel_file);
}

// Fungsi untuk membaca data dari CSV
function readData($file) {
    $data = [];
    if (file_exists($file) && filesize($file) > 0) {
        $handle = fopen($file, 'r');
        $headers = fgetcsv($handle); // Skip header
        
        $id = 1;
        while (($row = fgetcsv($handle)) !== false) {
            // Pastikan array memiliki 6 elemen
            if (count($row) >= 6) {
                $data[] = [
                    'id' => $id++,
                    'tanggal' => isset($row[0]) ? $row[0] : '',
                    'nama' => isset($row[1]) ? $row[1] : '',
                    'jenis' => isset($row[2]) ? $row[2] : '',
                    'kategori' => isset($row[3]) ? $row[3] : '',
                    'jumlah' => isset($row[4]) ? $row[4] : '0',
                    'keterangan' => isset($row[5]) ? $row[5] : ''
                ];
            }
        }
        fclose($handle);
    }
    return $data;
}

// Fungsi untuk menyimpan semua data ke CSV
function saveAllData($file, $all_data) {
    $handle = fopen($file, 'w');
    
    // Tulis header
    fputcsv($handle, ['Tanggal', 'Nama', 'Jenis', 'Kategori', 'Jumlah', 'Keterangan']);
    
    // Tulis semua data
    foreach ($all_data as $row) {
        fputcsv($handle, [
            $row['tanggal'],
            $row['nama'],
            $row['jenis'],
            $row['kategori'],
            $row['jumlah'],
            $row['keterangan']
        ]);
    }
    
    fclose($handle);
}

// Fungsi untuk menambah data ke CSV
function addData($file, $data) {
    // Cek apakah file kosong atau belum ada
    $is_new_file = !file_exists($file) || filesize($file) == 0;
    
    $handle = fopen($file, 'a');
    
    // Jika file baru, tulis header
    if ($is_new_file) {
        fputcsv($handle, ['Tanggal', 'Nama', 'Jenis', 'Kategori', 'Jumlah', 'Keterangan']);
    }
    
    fputcsv($handle, $data);
    fclose($handle);
}

// Proses CRUD
$message = '';
$edit_data = null;

// CREATE - Tambah data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $tanggal = isset($_POST['tanggal']) ? $_POST['tanggal'] : '';
    $nama = isset($_POST['nama']) ? $_POST['nama'] : '';
    $jenis = isset($_POST['jenis']) ? $_POST['jenis'] : '';
    $kategori = isset($_POST['kategori']) ? $_POST['kategori'] : '';
    $jumlah = isset($_POST['jumlah']) ? $_POST['jumlah'] : '';
    $keterangan = isset($_POST['keterangan']) ? $_POST['keterangan'] : '';
    
    // Validasi
    if (!empty($tanggal) && !empty($nama) && !empty($jenis) && !empty($kategori) && !empty($jumlah)) {
        $data = [$tanggal, $nama, $jenis, $kategori, $jumlah, $keterangan];
        addData($excel_file, $data);
        
        // Redirect untuk mencegah resubmit
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=add");
        exit;
    }
}

// UPDATE - Edit data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
    $tanggal = isset($_POST['tanggal']) ? $_POST['tanggal'] : '';
    $nama = isset($_POST['nama']) ? $_POST['nama'] : '';
    $jenis = isset($_POST['jenis']) ? $_POST['jenis'] : '';
    $kategori = isset($_POST['kategori']) ? $_POST['kategori'] : '';
    $jumlah = isset($_POST['jumlah']) ? $_POST['jumlah'] : '';
    $keterangan = isset($_POST['keterangan']) ? $_POST['keterangan'] : '';
    
    if (!empty($tanggal) && !empty($nama) && !empty($jenis) && !empty($kategori) && !empty($jumlah)) {
        $all_data = readData($excel_file);
        
        // Update data berdasarkan ID
        foreach ($all_data as $key => $row) {
            if ($row['id'] == $edit_id) {
                $all_data[$key]['tanggal'] = $tanggal;
                $all_data[$key]['nama'] = $nama;
                $all_data[$key]['jenis'] = $jenis;
                $all_data[$key]['kategori'] = $kategori;
                $all_data[$key]['jumlah'] = $jumlah;
                $all_data[$key]['keterangan'] = $keterangan;
                break;
            }
        }
        
        // Simpan kembali semua data
        saveAllData($excel_file, $all_data);
        
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=update");
        exit;
    }
}

// DELETE - Hapus data
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $all_data = readData($excel_file);
    
    // Filter data yang tidak dihapus
    $filtered_data = array_filter($all_data, function($row) use ($delete_id) {
        return $row['id'] != $delete_id;
    });
    
    // Simpan kembali data yang tersisa
    saveAllData($excel_file, $filtered_data);
    
    header("Location: " . $_SERVER['PHP_SELF'] . "?success=delete");
    exit;
}

// EDIT - Ambil data untuk diedit
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $all_data = readData($excel_file);
    
    foreach ($all_data as $row) {
        if ($row['id'] == $edit_id) {
            $edit_data = $row;
            break;
        }
    }
}

// Tampilkan pesan sukses
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'add':
            $message = 'Data berhasil ditambahkan!';
            break;
        case 'update':
            $message = 'Data berhasil diupdate!';
            break;
        case 'delete':
            $message = 'Data berhasil dihapus!';
            break;
    }
}

// Baca semua data
$all_data = readData($excel_file);

// Hitung total dengan validasi
$total_pemasukan = 0;
$total_pengeluaran = 0;

foreach ($all_data as $row) {
    // Pastikan jumlah adalah angka
    $jumlah = is_numeric($row['jumlah']) ? floatval($row['jumlah']) : 0;
    
    if (isset($row['jenis']) && $row['jenis'] == 'Pemasukan') {
        $total_pemasukan += $jumlah;
    } elseif (isset($row['jenis']) && $row['jenis'] == 'Pengeluaran') {
        $total_pengeluaran += $jumlah;
    }
}

$saldo = $total_pemasukan - $total_pengeluaran;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Keuangan CRUD</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 10px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 14px;
        }
        .form-section {
            padding: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
            font-size: 14px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border 0.3s;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: transform 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            width: 100%;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .btn-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .btn-danger {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        .btn-small {
            padding: 8px 12px;
            font-size: 11px;
            width: auto;
        }
        .summary {
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            background: #f8f9fa;
        }
        .summary-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .summary-card h3 {
            color: #666;
            font-size: 13px;
            margin-bottom: 8px;
        }
        .summary-card .amount {
            font-size: 20px;
            font-weight: bold;
        }
        .income { color: #38ef7d; }
        .expense { color: #ff6b6b; }
        .balance { color: #667eea; }
        .data-section {
            padding: 20px;
        }
        .data-section h2 {
            font-size: 18px;
            margin-bottom: 15px;
        }
        
        /* Table Responsive */
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-top: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        table th,
        table td {
            padding: 10px 8px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }
        table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        table tr:hover {
            background: #f8f9fa;
        }
        
        /* Card View for Mobile */
        .card-view {
            display: none;
        }
        .transaction-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .transaction-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        .transaction-card .card-date {
            font-weight: bold;
            color: #667eea;
        }
        .transaction-card .card-type {
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
        }
        .transaction-card .card-type.income {
            background: #d4edda;
            color: #155724;
        }
        .transaction-card .card-type.expense {
            background: #f8d7da;
            color: #721c24;
        }
        .transaction-card .card-body {
            margin-bottom: 10px;
        }
        .transaction-card .card-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .transaction-card .card-label {
            color: #666;
            font-weight: bold;
        }
        .transaction-card .card-value {
            color: #333;
        }
        .transaction-card .card-amount {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
            text-align: right;
        }
        
        .alert {
            padding: 12px;
            margin: 15px 20px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 15px;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .no-data {
            text-align: center;
            padding: 30px;
            color: #999;
            font-size: 14px;
        }
        .edit-mode {
            background: #fff3cd;
            border: 2px solid #ffc107;
        }
        .edit-title {
            color: #856404;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        /* Tablet */
        @media (min-width: 768px) {
            body {
                padding: 20px;
            }
            .header h1 {
                font-size: 28px;
            }
            .header p {
                font-size: 16px;
            }
            .form-section {
                padding: 30px;
            }
            .form-row {
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }
            .summary {
                padding: 30px;
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
            }
            .summary-card {
                padding: 20px;
            }
            .summary-card h3 {
                font-size: 14px;
            }
            .summary-card .amount {
                font-size: 24px;
            }
            .data-section {
                padding: 30px;
            }
            .data-section h2 {
                font-size: 20px;
            }
            .btn {
                width: auto;
            }
            .btn-group {
                flex-direction: row;
            }
        }
        
        /* Mobile - Show Card View */
        @media (max-width: 767px) {
            .table-wrapper {
                display: none;
            }
            .card-view {
                display: block;
            }
            .action-buttons {
                width: 100%;
            }
            .action-buttons .btn-small {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 Aplikasi Pencatatan Keuangan</h1>
            <p>Kelola Pemasukan dan Pengeluaran Anda dengan CRUD</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success">
            ✓ <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="form-section <?php echo $edit_data ? 'edit-mode' : ''; ?>">
            <?php if ($edit_data): ?>
            <h2 class="edit-title">✏️ Edit Transaksi #<?php echo $edit_data['id']; ?></h2>
            <?php else: ?>
            <h2 style="margin-bottom: 20px;">➕ Tambah Transaksi Baru</h2>
            <?php endif; ?>
            
            <form method="POST" action="">
                <?php if ($edit_data): ?>
                <input type="hidden" name="edit_id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" 
                               value="<?php echo $edit_data ? $edit_data['tanggal'] : date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" name="nama" placeholder="Nama lengkap" 
                               value="<?php echo $edit_data ? htmlspecialchars($edit_data['nama']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Jenis Transaksi</label>
                        <select name="jenis" required>
                            <option value="">Pilih Jenis</option>
                            <option value="Pemasukan" <?php echo ($edit_data && $edit_data['jenis'] == 'Pemasukan') ? 'selected' : ''; ?>>Pemasukan</option>
                            <option value="Pengeluaran" <?php echo ($edit_data && $edit_data['jenis'] == 'Pengeluaran') ? 'selected' : ''; ?>>Pengeluaran</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <input type="text" name="kategori" placeholder="Contoh: Gaji, Belanja, Transport" 
                               value="<?php echo $edit_data ? htmlspecialchars($edit_data['kategori']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Jumlah (Rp)</label>
                    <input type="number" name="jumlah" placeholder="0" min="0" step="1" 
                           value="<?php echo $edit_data ? $edit_data['jumlah'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan" rows="3" placeholder="Keterangan tambahan (opsional)"><?php echo $edit_data ? htmlspecialchars($edit_data['keterangan']) : ''; ?></textarea>
                </div>
                
                <div class="btn-group">
                    <?php if ($edit_data): ?>
                    <button type="submit" name="update" class="btn btn-warning">💾 Update Transaksi</button>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-danger">❌ Batal</a>
                    <?php else: ?>
                    <button type="submit" name="submit" class="btn">➕ Simpan Transaksi</button>
                    <?php endif; ?>
                    <?php if (file_exists($excel_file) && filesize($excel_file) > 0): ?>
                    <a href="?download_excel=1" class="btn btn-success">📥 Download Excel (Format Rapi)</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="summary">
            <div class="summary-card">
                <h3>Total Pemasukan</h3>
                <div class="amount income">Rp <?php echo number_format($total_pemasukan, 0, ',', '.'); ?></div>
            </div>
            <div class="summary-card">
                <h3>Total Pengeluaran</h3>
                <div class="amount expense">Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?></div>
            </div>
            <div class="summary-card">
                <h3>Saldo</h3>
                <div class="amount balance">Rp <?php echo number_format($saldo, 0, ',', '.'); ?></div>
            </div>
        </div>

        <div class="data-section">
            <h2>📊 Riwayat Transaksi</h2>
            
            <?php if (count($all_data) > 0): ?>
            
            <!-- Table View (Desktop & Tablet) -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama</th>
                            <th>Jenis</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach (array_reverse($all_data) as $row): 
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($row['tanggal']))); ?></td>
                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                            <td>
                                <span style="color: <?php echo $row['jenis'] == 'Pemasukan' ? '#38ef7d' : '#ff6b6b'; ?>; font-weight: bold;">
                                    <?php echo htmlspecialchars($row['jenis']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                            <td style="font-weight: bold;">
                                Rp <?php echo number_format(is_numeric($row['jumlah']) ? $row['jumlah'] : 0, 0, ',', '.'); ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-warning btn-small">✏️ Edit</a>
                                    <a href="?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-danger btn-small" 
                                       onclick="return confirm('Yakin ingin menghapus data ini?')">🗑️ Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Card View (Mobile) -->
            <div class="card-view">
                <?php foreach (array_reverse($all_data) as $row): ?>
                <div class="transaction-card">
                    <div class="card-header">
                        <span class="card-date"><?php echo htmlspecialchars(date('d/m/Y', strtotime($row['tanggal']))); ?></span>
                        <span class="card-type <?php echo $row['jenis'] == 'Pemasukan' ? 'income' : 'expense'; ?>">
                            <?php echo htmlspecialchars($row['jenis']); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="card-row">
                            <span class="card-label">Nama:</span>
                            <span class="card-value"><?php echo htmlspecialchars($row['nama']); ?></span>
                        </div>
                        <div class="card-row">
                            <span class="card-label">Kategori:</span>
                            <span class="card-value"><?php echo htmlspecialchars($row['kategori']); ?></span>
                        </div>
                        <div class="card-row">
                            <span class="card-label">Keterangan:</span>
                            <span class="card-value"><?php echo htmlspecialchars($row['keterangan']); ?></span>
                        </div>
                        <div class="card-amount">
                            Rp <?php echo number_format(is_numeric($row['jumlah']) ? $row['jumlah'] : 0, 0, ',', '.'); ?>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-warning btn-small">✏️ Edit</a>
                        <a href="?delete=<?php echo $row['id']; ?>" 
                           class="btn btn-danger btn-small" 
                           onclick="return confirm('Yakin ingin menghapus data ini?')">🗑️ Hapus</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php else: ?>
            <div class="no-data">
                <p>📋 Belum ada data transaksi</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>