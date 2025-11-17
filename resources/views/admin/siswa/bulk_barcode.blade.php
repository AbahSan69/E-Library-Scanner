<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; }

        .grid {
            display: flex;
            flex-wrap: wrap;
        }
        .label {
            width: 5.5cm;
            height: 2.5cm;
            border: 1px solid #ddd;
            margin: 4px;
            padding: 4px;
            text-align: center;
            box-sizing: border-box;
        }
        .school {
            font-size: 10px;
            font-weight: bold;
        }
        .nisn {
            font-size: 10px;
            margin-top: 2px;
        }
        img {
            margin-top: 3px;
        }
    </style>
</head>
<body>

<div class="grid">
    @foreach($siswa as $row)
        <div class="label">
            <div class="school">SMAN 1 CITEUREUP</div>

            <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($row->nisn, 'C128') }}"
                 height="25">

            <div class="nisn">{{ $row->nisn }}</div>
        </div>
    @endforeach
</div>

</body>
</html>
