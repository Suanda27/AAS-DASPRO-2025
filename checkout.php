<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pseudocode 1: Memeriksa apakah data checkout dikirim melalui POST dan mendekode data JSON
    // Jika metode request adalah POST, ambil data checkout dalam format JSON
    // Dekode data JSON menjadi array PHP
    $checkoutData = json_decode($_POST['checkoutData'], true);
    
    if ($checkoutData) {
        // Mendapatkan data nama dan alamat
        $name = $_POST['name'];
        $address = $_POST['address'];
        $totalPrice = $checkoutData['totalPrice'];
        $items = $checkoutData['items'];
        
        // Pseudocode 2: Memeriksa jumlah item dan menggabungkan tipe dan ukuran yang unik
        // Jika hanya ada satu item, ambil tipe dan ukuran dari item tersebut
        // Jika ada lebih dari satu item, gabungkan tipe dan ukuran yang unik
        if (count($items) === 1) {
            // Jika hanya ada satu item, ambil type dan size dari item tersebut
            $item = $items[0];
            $type = $item['type'];
            $size = $item['size'];
        } else {
            // Jika ada lebih dari satu item, gabungkan semua type dan size yang unik
            $types = array_map(function($item) { return $item['type']; }, $items);
            $sizes = array_map(function($item) { return $item['size']; }, $items);
            $type = implode(', ', array_unique($types)); // Gabungkan type yang unik
            $size = implode(', ', array_unique($sizes)); // Gabungkan size yang unik
        }

        // Query untuk memasukkan data pesanan ke dalam database
        $stmt = $conn->prepare("INSERT INTO orders (name, address, date, price, status, type, size) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        // Menentukan tanggal dan status pesanan
        $date = date('Y-m-d');
        $status = 'In Progress';
        
        // Mengikat parameter dan mengeksekusi query untuk menyimpan data pesanan
        $stmt->bind_param("sssdsss", 
            $name,
            $address,
            $date,
            $totalPrice,
            $status,
            $type,
            $size
        );

        // Mengecek apakah query berhasil dijalankan
        if ($stmt->execute()) {
            // Jika berhasil, kirimkan respon sukses
            echo json_encode(["success" => true, "message" => "Order created successfully"]);
        } else {
            // Jika gagal, kirimkan respon error
            echo json_encode(["success" => false, "message" => "Error creating order"]);
        }
    }
}
?>
