<?php
function renderView($paramsHeader, $paramsBody, $urlView)
{
    Flight::render("header.php", $paramsHeader);
    Flight::render($urlView, $paramsBody);
    Flight::render("footer.php");
}

function getApiResponse($url, $param)
{
    $jsonParam = json_encode($param);

    // send curl request
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_PORT => "",
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $jsonParam,
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "Content-type: application/json"
        )
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) 
        return "cURL Error #:" . $err;
    else 
        return json_decode($response);
}

function toRupiah($price)
{
    return number_format($price + 0, 0, "", ".");
}

function toRupiahBlank($price)
{
    if (trim($price) == "" || $price == 0)
        return "";
    
    return number_format($price + 0, 0, "", ".");
}

function zeroToBlank($val)
{
    if ($val == 0) 
        return "";

    return (int) $val;
}

function rpTerbilang($x)
{	
    $ambil = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
    if($x < 12){
        return " " . $ambil[$x];
    }else if ($x < 20){
        return rpTerbilang($x - 10) . " belas";
    }else if ($x < 20){
        return rpTerbilang($x - 10) . " belas";
    }else if ($x < 100){
        return rpTerbilang($x / 10) . " puluh" . rpTerbilang($x % 10);
    }else if ($x < 200){
            return " seratus" . rpTerbilang($x - 100);
    }else if ($x < 1000){
            return rpTerbilang($x / 100) . " ratus" . rpTerbilang($x % 100);
    }else if ($x < 2000){
            return " seribu" . rpTerbilang($x - 1000);
    }else if ($x < 1000000){
            return rpTerbilang($x / 1000) . " ribu" . rpTerbilang($x % 1000);
    } else if ($x < 1000000000){
            return rpTerbilang($x / 1000000) . " juta" . rpTerbilang($x % 1000000);
    }
}

function toRojbDate($mysqlDate)
{
    $year = substr($mysqlDate, 0, 4);
    $month = substr($mysqlDate, 5, 2);
    $day = substr($mysqlDate, 8, 2);

    if (strlen($year) == 0 && strlen($month) == 0 && strlen($day) == 0)
        return "";

    return $day . "/" . $month . "/" . $year;
}

function isValidDate($date)
{
    $d = DateTime::createFromFormat("Y-m-d", $date);
    return $d && $d->format("Y-m-d") === $date;
}

function strReplaceFirst($from, $to, $subject)
{
    $from = '/'.preg_quote($from, '/').'/';

    return preg_replace($from, $to, $subject, 1);
}

function cleanString($string) 
{
   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

   return preg_replace('/[^A-Za-z0-9.\-]/', '', $string); // Removes special chars.
}

function randomString($panjang)
{
   $karakter = 'abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ123456789';
   $string = '';
   for($i = 0; $i < $panjang; $i++)
   {
	   $pos = rand(0, strlen($karakter)-1);
	   $string .= $karakter{$pos};
   }
   return $string;
}

class PDF_MC_Table extends FPDF
{
    var $widths;
    var $aligns;

    function SetWidths($w)
    {
        //Set the array of column widths
        $this->widths=$w;
    }

    function SetAligns($a)
    {
        //Set the array of column alignments
        $this->aligns=$a;
    }

    function Row($data)
    {
        //Calculate the height of the row
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h=5*$nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for($i=0;$i<count($data);$i++)
        {
            $w=$this->widths[$i];
            $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            //Save the current position
            $x=$this->GetX();
            $y=$this->GetY();
            //Draw the border
            $this->Rect($x,$y,$w,$h);
            //Print the text
            $this->MultiCell($w,5,$data[$i],0,$a);
            //Put the position to the right of the cell
            $this->SetXY($x+$w,$y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        //If the height h would cause an overflow, add a new page immediately
        if($this->GetY()+$h>$this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w,$txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw=&$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while($i<$nb)
        {
            $c=$s[$i];
            if($c=="\n")
            {
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep=$i;
            $l+=$cw[$c];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i=$sep+1;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
}