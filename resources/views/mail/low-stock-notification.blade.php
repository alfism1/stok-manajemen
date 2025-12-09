<!DOCTYPE html>
<html>
<head>
    <title>Peringatan Stok Menipis</title>
</head>
<style>
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>
<body>
    <h2>Daftar Barang dengan Stok Menipis</h2>
    <p>Halo Admin, berikut adalah daftar barang yang stoknya sudah di bawah minimum:</p>
    
    <table>
        <thead>
            <tr>
                <th>Nama Barang</th>
                <th>SKU</th>
                <th>Stok Saat Ini</th>
                <th>Stok Minimum</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->sku }}</td>
                <td style="color: red; font-weight: bold;">{{ $item->current_stock }}</td>
                <td>{{ $item->minimum_stock }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <p>Mohon segera lakukan restock.</p>
</body>
</html>
