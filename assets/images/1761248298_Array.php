<head>
    <title>
        <?php
        echo 'Romanes eunt domos';
        ?>
    </title>
</head>
    <body>
        <?php
        $arr = [
            '1' => 'eins',
            '2' => 'zwei',
            '3' => 'drei',
        ];

        foreach ($arr as $zahl){
            echo $zahl. ',';
        }
        ?>
    </body>
</html>