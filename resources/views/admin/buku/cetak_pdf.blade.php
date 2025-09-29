<!DOCTYPE html>
<html>
<head>
    <title>Label Buku</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .label {
            width: 200px;
            height: 60px;
            border: 1px solid #000;
            padding: 5px;
            margin: auto;
        }
    </style>
</head>
<body>
    <div class="label">
        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($book->no_barcode, 'EAN13', 2, 50, [0,0,0], true) }}"><br>
    </div>
</body>
</html>
