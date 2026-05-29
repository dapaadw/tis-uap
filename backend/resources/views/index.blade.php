<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WowoClean</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .error { color: red; font-size: 0.8em; margin-bottom: 10px; display: block; }
        .success { color: green; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>

    <h2>WowoClean</h2>
    
    <h3>Input Kontainer</h3>
    <form id="containerForm">
        <div>
            <label>Container ID:</label><br>
            <input type="text" id="container_id" placeholder="Cth: GD12345">
            <span class="error" id="err_container_id"></span>
        </div>
        
        <div>
            <label>Waste Type:</label><br>
            <input type="text" id="waste_type" placeholder="Cth: Chemical, Glass, Metal">
            <span class="error" id="err_waste_type"></span>
        </div>
        
        <div>
            <label>Weight (Kg):</label><br>
            <input type="number" id="weight_kg" placeholder="10 - 5000">
            <span class="error" id="err_weight_kg"></span>
        </div>
        
        <button type="submit">Simpan Data</button>
    </form>

    <hr>

    <h3>Daftar Kontainer</h3>
    <strong>Total Muatan: <span id="totalWeight">0</span> Kg</strong>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tipe</th>
                <th>Berat (Kg)</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="dataTable">
            </tbody>
    </table>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>