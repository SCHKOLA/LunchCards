<?php
/*
 * The MIT License
 *
 * Copyright 2016 - 2019 Niklas Merkelt
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

function createPDF($array) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    
    $pdf = new TCPDF();

    $pdf->setHeaderData('', 0, '', '', array(255, 255, 255), array(255, 255, 255));
    $pdf->setFooterData(array(255, 255, 255), array(255, 255, 255));

    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AddPage();
    $pdf->SetFont('times');
    $pdf->SetAuthor('LunchCard Creator');
    $pdf->SetTitle('LunchCard Creator');

    for ($i = 0; $i < count($array); $i++) {
        $row = floor(($i - (12 * ($pdf->PageNo() - 1))) / 2);
        $pdf->SetY(10 + (45 * $row));
        if ($i % 2 == 0) {
            $pdf->SetX(10);
        } else {
            $pdf->SetX(120);
        }
        $pdf->Cell(80, 10, $array[$i][1], 'TLR', 2, 'C');
        $pdf->Cell(80, 9, $array[$i][2], 'TLR', 2, 'C');
        $pdf->Cell(80, 20, '', 'RL', 2);
        $pdf->Cell(80, 5, 'XBA: ' . $array[$i][0], 'BLR', 2, 'R');
        $rowRecalc = floor(($i - (12 * ($pdf->PageNo() - 1))) / 2);
        if ($i % 2 == 0) {
            $pdf->write2DBarcode($array[$i][0], 'QRCODE,Q', 38, 28 + 45 * $rowRecalc, 24, 24);
        } else {
            $pdf->write2DBarcode($array[$i][0], 'QRCODE,Q', 148, 28 + 45 * $rowRecalc, 24, 24);
        }
    }

    $pdf->Output('LLC.pdf');
}

if (filter_has_var(INPUT_GET, 'xba')) {

    $json = json_decode(filter_input(INPUT_GET, 'xba'));

    createPDF($json);

} else {
    ?>
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="UTF-8">
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
                <select id="function">
                    <option value="Schüler">Schüler</option>
                    <option value="Mitarbeiter">Mitarbeiter</option>
                    <option value="Gast">Gast</option>
                </select>
                <input type="submit" value="Hinzufügen"/>
            </form>
            <form id="send">
                <input type="submit" value="Generieren"/>
            </form>
            <script>
                var result = new Array();
                var F_xba = document.getElementById("xba");
                var F_name = document.getElementById("name");
                var F_function = document.getElementById("function");
                document.getElementById("addRow").addEventListener("submit", function (e) {
                    e.preventDefault();
                    document.querySelector("table tbody").innerHTML += "<tr><td>" + F_xba.value + "</td><td>" + F_name.value + "</td><td>" + F_function.value + "</td></tr>";
                    result.push(new Array(F_xba.value, F_name.value, F_function.value));
                    F_xba.value = "";
                    F_name.value = "";
                });
                document.getElementById("send").addEventListener("submit", function (e) {
                    e.preventDefault();
                    if (result.length === 0) {
                        alert("Keine Elemente übergeben!");
                        return;
                    }
                    window.location = "index.php?xba=" + JSON.stringify(result);
                });
            </script>
        </body>
    </html>
    <?php
}
