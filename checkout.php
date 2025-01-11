<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Pseudocode 1: Memeriksa apakah data checkout dikirim melalui POST dan mendekode data JSON
        // Jika metode request adalah POST, ambil data checkout dalam format JSON
        // Dekode data JSON menjadi array PHP
        $checkoutData = json_decode($_POST['checkoutData'], true);

        // Mengecek apakah decoding JSON berhasil
        if ($checkoutData === null) {
            throw new Exception("Invalid JSON data received");
        }

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
                $item = $items[0];
                $type = $item['type'];
                $size = $item['size'];
            } else {
                $types = array_map(function($item) { return $item['type']; }, $items);
                $sizes = array_map(function($item) { return $item['size']; }, $items);
                $type = implode(', ', array_unique($types)); // Gabungkan type yang unik
                $size = implode(', ', array_unique($sizes)); // Gabungkan size yang unik
            }

            // Query untuk memasukkan data pesanan ke dalam database
            $stmt = $conn->prepare("INSERT INTO orders (name, address, date, price, status, type, size) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");

            if (!$stmt) {
                throw new Exception("Database query preparation failed: " . $conn->error);
            }

            // Menentukan tanggal dan status pesanan
            $date = date('Y-m-d');
            $status = 'In Progress';

            // Mengikat parameter dan mengeksekusi query untuk menyimpan data pesanan
            $stmt->bind_param("sssdsss", $name, $address, $date, $totalPrice, $status, $type, $size);

            // Mengecek apakah query berhasil dijalankan
            if (!$stmt->execute()) {
                throw new Exception("Error executing query: " . $stmt->error);
            }

            // Jika berhasil, kirimkan respon sukses
            echo json_encode(["success" => true, "message" => "Order created successfully"]);
        }
    } catch (Exception $e) {
        // Tangani exception dan kirimkan respon error
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
}
?>
