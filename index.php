<?php
/*
 * The MIT License
 *
 * Copyright 2016 Niklas Merkelt
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

if (isset($_GET['xba'])) {
    require_once 'phpqrcode.php';
    define('FPDF_FONTPATH', 'fpdf/font/');
    define('FPDF_INSTALLDIR', 'fpdf/');
    include(FPDF_INSTALLDIR . 'fpdf.php');

    $json = json_decode($_GET['xba']);

    if (count($json) > 12) {
        echo "Fehler: Es wurden mehr als 12 Elemente hinzugefügt!";
        exit;
    }

    if (!file_exists('qrcodes')) {
        mkdir('qrcodes', 0777, true);
    }

    foreach ($json as $XBA) {
        if (!file_exists("qrcodes/" . $XBA[0] . ".png")) {
            QRcode::png($XBA[0], "qrcodes/" . $XBA[0] . ".png", QR_ECLEVEL_M, 4, 1);
        }
    }

    $pdf = new FPDF();
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->AddPage();
    $pdf->SetFont('times');
    $pdf->SetCreator('FPDF www.fpdf.de');
    $pdf->SetAuthor('LunchCard Creator');
    $pdf->SetTitle('LunchCard Creator');

    $j = 0;
    for ($i = 0; $i < ceil(count($json) / 2); $i++) {
        $pdf->Cell(80, 10, utf8_decode($json[$j][1]), 1, 0, 'C');
        $pdf->Cell(30, 10, '', 0, 0);
        $pdf->Cell(80, 10, utf8_decode($json[$j + 1][1]), 1, 1, 'C');
        $pdf->Cell(80, 10, utf8_decode($json[$j][2]), 'TRL', 0, 'C');
        $pdf->Cell(30, 10, '', 0, 0);
        $pdf->Cell(80, 10, utf8_decode($json[$j + 1][2]), 'TRL', 1, 'C');
        $pdf->Cell(80, 20, '', 'RL', 0);
        $pdf->Cell(30, 10, '', 0, 0);
        $pdf->Cell(80, 20, '', 'RL', 1);
        $pdf->Cell(80, 5, 'XBA: ' . $json[$j][0], 'BLR', 0, 'R');
        $pdf->Cell(30, 10, '', 0, 0);
        $pdf->Cell(80, 5, 'XBA: ' . $json[$j + 1][0], 'BLR', 1, 'R');
        $pdf->Image('qrcodes/' . $json[$j][0] . ".png", 38, 28 + 45 * $i);
        $pdf->Image('qrcodes/' . $json[$j + 1][0] . ".png", 148, 28 + 45 * $i);
        $j = $j + 2;
    }

    $pdf->Output('I', 'test.pdf');
} else {
    ?>
    <!DOCTYPE html>
    <html>
        <head>
            <title>LunchCard Creator</title>
        </head>
        <body>
            <table rules="cols" style="width: 300px">
                <thead>
                    <tr style="border-bottom: 1px solid #000">
                        <td>XBA</td><td>Name</td><td>Funktion</td>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
            <br>
            <form id="addRow">
                <input id="xba" type="text" placeholder="XBA-Nummer" required=""/>
                <input id="name" type="text" placeholder="Name" required=""/>
                <select id="funktion">
                    <option value="Schüler">Schüler</option>
                    <option value="Mitarbeiter">Mitarbeiter</option>
                </select>
                <input type="submit" value="Hinzufügen"/>
            </form>
            <form id="send">
                <input type="submit" value="Generieren"/>
            </form>
            <script type="text/javascript" src="jquery-3.1.1.min.js"></script>
            <script>
                var result = new Array();
                $("#addRow").submit(function (e) {
                    e.preventDefault();
                    if (result.length > 12) {
                        alert("Nicht mehr als 12 Elemente möglich!");
                        return;
                    }
                    $("table tbody").append("<tr><td>" + $("#xba").val() + "</td><td>" + $("#name").val() + "</td><td>" + $("#funktion").val() + "</td></tr>");
                    result.push(new Array($("#xba").val(), $("#name").val(), $("#funktion").val()));
                    $("#xba").val("");
                    $("#name").val("");
                });
                $("#send").submit(function (e) {
                    e.preventDefault();
                    if (result.length === 0) {
                        alert("Keine Elemente übergeben!");
                        return;
                    }
                    if (result.length % 2 !== 0) {
                        result.push(new Array("0000", "MUSTER", "MUSTER"));
                    }
                    window.location = "index.php?xba="+JSON.stringify(result);
                });
            </script>
        </body>
    </html>
    <?php
}