<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Barcode Buku</title>
    <style>
        /* --- Ukuran kertas custom: 14x7 cm --- */
        @page {
            size: 14cm 7cm;
            margin: 0;
        }

        body {
            font-family: sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            width: 14cm;
            height: 7cm;
            display: flex;
            justify-content: center;  /* horizontal center */
            align-items: center;      /* vertical center */
            text-align: center;
        }

        .label {
            width: 13cm;
            height: auto;
            padding: 0.2cm;
            box-sizing: border-box;
            text-align: center;
        }

        .school-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2mm;
        }

        .book-title {
            font-size: 10px;
            font-weight: 600;
            margin-bottom: 2mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .barcode img {
            width: 90%; /* supaya tidak keluar dari batas kertas */
            height: auto;
        }

        .barcode-number {
            font-size: 12px;
            letter-spacing: 2px;
            margin-top: 2mm;
        }
    </style>
</head>
<body>
    <div class="label">
        <div class="school-name">SMAN 1 CITEUREUP</div>

        <div class="book-title">{{ $book->judul }}</div>

        <div class="barcode">
            <img src="data:image/png;base64,{{ 
                DNS1D::getBarcodePNG($book->no_barcode, 'EAN13', 3, 100, [0,0,0], true) 
            }}" alt="barcode">
        </div>

        <div class="barcode-number">{{ $book->no_barcode }}</div>
    </div>
</body>
</html>
