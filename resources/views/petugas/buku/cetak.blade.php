<!DOCTYPE html>
<html>
<head>
    <title>Cetak Label Buku</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 0;
        }
        .label {
            width: 200px;
            height: 120px;
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            margin: 20px auto;

            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .label img {
            max-width: 100%;
            max-height: 70%;
            margin: 5px 0;
        }

        .school-name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body onload="window.print()">

    <div class="label">
        {{-- Nama Sekolah --}}
        <div class="school-name">SMAN 1 CITEUREUP</div>
        
        {{-- Barcode --}}
        <img 
            src="data:image/png;base64,{{ DNS1D::getBarcodePNG($book->no_barcode, 'EAN13', 2, 50, [0,0,0], true) }}" 
            alt="barcode">

        {{-- Nomor Barcode --}}
        <small>{{ $book->no_barcode }}</small>
    </div>

</body>
</html>
