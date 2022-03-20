<?php 
    /*
        THIS SCRIPT IS FOR GARNI WEATHER STATION USERS TO SAVE DATA FROM THE WEATHER STATION TO PERSONAL WEB SERVER
        TENTO SKRIPT JE PRO UŽIVATELE METEOSTANIC GARNI PRO UKLÁDÁNÍ DAT NA VLASTNÍ WEBOVÝ SERVER

        Instructions/instrukce:
        This script MUST be saved in a folder named "weatherstation" in the server root. Also this script must not be renamed. Name of the folder and script is automatically assumed by the weather station and cannot be changed.
        Tento skript MUSÍ být uložen ve složce pojmenované "weatherstation" v kořenovém adresáři serveru. Tento skript také nesmí být přejmenován - meteostanice má předdefinované tyto názvy a nelze je změnit.

        Fill in the parameters below. 
        Nastavte parametry níže.

        Author/Autor: Jáchym Brzezina, https://www.ovzdusi.cz, https://www.envidata.cz, https://www.infoviz.cz
        Version/Verze: 1.0
        Date/Datum: 2022-03-20
        
    */

    // language of the output file / jazyk výstupního souboru
    $language = "cz"; // en (English), cz (Czech)

    // units / jednotky
    $temperatureUnits = "C"; // C/F; temperature units / jednotky teplot
    $windUnits = "kmh"; // kmh/ms/mph/kt; wind speed units / jednotky rychlosti větru
    $pressureUnits = "hpa"; // hpa/inhg/mmhg; pressure units / jednotky tlaku
    $rainUnits = "mm"; // mm/in; precipitation units / jednotky srážek

    // password provided in weather station setup (leave empty if not set)
    // heslo zadané v nastavení vlastního serveru v nastavení stanice (neměňte, pokud není nastavené)
    $password = "";

    // string to replace missing values, can be left empty, or set for example to "--" or "-999" etc.
    // symbol pro chybějící hodnotu - prázdný, "--" apod.
    $empty = "";

    // adjust parameters
    // korekce parametrů
    $pressureCorrection = "0"; // pressure correction in hPa / korekce tlaku v hPa
    $windDirCorrection = "0"; // wind direction correction / korekce směru větru

    /* ----------------------- END SETUP / KONEC NASTAVENÍ --------------------------------- */

    if(trim($_GET['PASSWORD']) != $password){
        echo "incorrect password / neplatne heslo";
        die();
    }

    if($windUnits == "kmh"){
        $windUnitsFormatted = "km/h";
    }
    if($windUnits == "ms"){
        $windUnitsFormatted = "m/s";
    }
    if($pressureUnits == "hpa"){
        $pressureUnitsFormatted = "hPa";
    }
    if($pressureUnits == "inhg"){
        $pressureUnitsFormatted = "inHg";
    }
    if($pressureUnits == "mmhg"){
        $pressureUnitsFormatted = "mmHg";
    }

    if($language == "cz"){
        $parameters = array();
        $parameters[] = "Datum";
        $parameters[] = "Čas";
        $parameters[] = "Teplota [°".trim(strtoupper($temperatureUnits))."]";
        $parameters[] = "Vlhkost [%]";
        $parameters[] = "Rosný bod [°".trim(strtoupper($temperatureUnits))."]";
        $parameters[] = "Tlak [".$pressureUnitsFormatted."]";
        $parameters[] = "Průměrná rychlost větru [".$windUnitsFormatted."]";
        $parameters[] = "Náraz větru [".$windUnitsFormatted."]";
        $parameters[] = "Směr větru [°]";
        $parameters[] = "Směr větru";
        $parameters[] = "Denní srážky [".$rainUnits."]";
        $parameters[] = "Solární radiace [W/m2]";
        $parameters[] = "UV";
        $parameters[] = "Pocitová teplota [°".trim(strtoupper($temperatureUnits))."]";
        $parameters[] = "Vnitřní teplota [°".trim(strtoupper($temperatureUnits))."]";
        $parameters[] = "Vnitřní vlhkost [%]";
    }
    if($language == "en"){
        $parameters = array();
        $parameters[] = "Date";
        $parameters[] = "Time";
        $parameters[] = "Temperature [°".trim(strtoupper($temperatureUnits))."]";
        $parameters[] = "Humidity [%]";
        $parameters[] = "Dewpoint [°".trim(strtoupper($temperatureUnits))."]";
        $parameters[] = "Pressure [".$pressureUnitsFormatted."]";
        $parameters[] = "Average wind speed [".$windUnitsFormatted."]";
        $parameters[] = "Wind gust [".$windUnitsFormatted."]";
        $parameters[] = "Wind direction [°]";
        $parameters[] = "Wind direction";
        $parameters[] = "Daily rain [".$rainUnits."]";
        $parameters[] = "Solar radiation [W/m2]";
        $parameters[] = "UV";
        $parameters[] = "Feels like [°".trim(strtoupper($temperatureUnits))."]";
        $parameters[] = "Indoor temperature [°".trim(strtoupper($temperatureUnits))."]";
        $parameters[] = "Indoor humidity [%]";
    }

    if(!file_exists(date("Y-m-d").".txt")){
        file_put_contents(date("Y-m-d").".txt", implode("\t", $parameters));
    }

    if(isset($_GET['tempf']) && is_numeric($_GET['tempf'])){
        $data['T'] = number_format(convertor($_GET['tempf'], "F", $temperatureUnits),1,".","");
    }
    if(isset($_GET['humidity']) && is_numeric($_GET['humidity'])){
        $data['H'] = number_format($_GET['humidity'],1,".","");
    }
    if(isset($data['T']) && isset($data['H'])){
        $data['D'] = number_format(convertor(dewpoint(convertor($_GET['tempf'], "F", "C"), $data['H']), "C", $temperatureUnits),1,".","");
    }
    if(isset($_GET['baromin']) && is_numeric($_GET['baromin'])){
        $data['P'] = number_format(convertor($_GET['baromin'], "inhg", $pressureUnits), 1,".","") + $pressureCorrection;
    }
    if(isset($_GET['windspeedmph']) && is_numeric($_GET['windspeedmph'])){
        $data['W'] = number_format(convertor($_GET['windspeedmph'], "mph", $windUnits),1,".","");
    }
    if(isset($_GET['windgustmph']) && is_numeric($_GET['windgustmph'])){
        $data['G'] = number_format(convertor($_GET['windgustmph'], "mph", $windUnits),1,".","");
    }
    if(isset($_GET['winddir']) && is_numeric($_GET['winddir'])){
        $data['B'] = number_format($_GET['winddir'] ,0,".","") + $windDirCorrection;
        if($data['B'] < 0){
            $data['B'] = $data['B'] + 360;
        }
        if($data['B'] > 360){
            $data['B'] = $data['B'] - 360;
        }
        $data['Babb'] = windAbb($data['B']);
    }
    if(isset($_GET['dailyrainin']) && is_numeric($_GET['dailyrainin'])){
        $data['R'] = number_format(convertor($_GET['dailyrainin'], "in", $rainUnits),1,".","");
    }
    if(isset($_GET['solarradiation']) && is_numeric($_GET['solarradiation'])){
        $data['S'] = number_format($_GET['tsolarradiation'],1,".","");
    }
    if(isset($_GET['UV']) && is_numeric($_GET['UV'])){
        $data['UV'] = number_format($_GET['tUV'],1,".","");
    }
    if(isset($_GET['indoortempf']) && is_numeric($_GET['indoortempf'])){
        $data['Tin'] = number_format(convertor($_GET['indoortempf'], "F", $temperatureUnits),1,".","");
    }
    if(isset($_GET['indoorhumidity']) && is_numeric($_GET['indoorhumidity'])){
        $data['Hin'] = number_format($_GET['indoorhumidity'],1,".","");
    }
    if(isset($data['T']) && isset($data['H']) && isset($data['W'])){
        $data['A'] = number_format(convertor(apparent(convertor($_GET['tempf'], "F", "C"), $data['H'], convertor($_GET['windspeedmph'], "mph", "ms")),"C", $temperatureUnits),1,".","");
    }

    $values = array();

    if(isset($data)){
        $values[] = date("Y-m-d");
        $values[] = date("H:i:s");
        if(isset($data['T'])){ $values[] = $data['T'];}else{$values[] = $empty;}
        if(isset($data['H'])){ $values[] = $data['H'];}else{$values[] = $empty;}
        if(isset($data['D'])){ $values[] = $data['D'];}else{$values[] = $empty;}
        if(isset($data['P'])){ $values[] = $data['P'];}else{$values[] = $empty;}
        if(isset($data['W'])){ $values[] = $data['W'];}else{$values[] = $empty;}
        if(isset($data['G'])){ $values[] = $data['G'];}else{$values[] = $empty;}
        if(isset($data['B'])){ $values[] = $data['B'];}else{$values[] = $empty;}
        if(isset($data['Babb'])){ $values[] = $data['Babb'];}else{$values[] = $empty;}
        if(isset($data['R'])){ $values[] = $data['R'];}else{$values[] = $empty;}
        if(isset($data['S'])){ $values[] = $data['S'];}else{$values[] = $empty;}
        if(isset($data['UV'])){ $values[] = $data['UV'];}else{$values[] = $empty;}
        if(isset($data['A'])){ $values[] = $data['A'];}else{$values[] = $empty;}
        if(isset($data['Tin'])){ $values[] = $data['Tin'];}else{$values[] = $empty;}
        if(isset($data['Hin'])){ $values[] = $data['Hin'];}else{$values[] = $empty;}
    }
    
    $file = fopen(date("Y-m-d").".txt", "a");
    fwrite($file, "\n".implode("\t", $values));
    fclose($file);

    function windAbb($value){
        global $language;
        if($language == "cz"){
            if($value<=11.25){
                return "S";
            }
            if($value>11.25 && $value<=33.75){
                return "SSV";
            }
            if($value>33.75 && $value<=56.25){
                return "SV";
            }
            if($value>56.25 && $value<=78.75){
                return "VSV";
            }
            if($value>78.75 && $value<=101.25){
                return "V";
            }
            if($value>101.25 && $value<=123.75){
                return "VJV";
            }
            if($value>123.75 && $value<=146.25){
                return "JV";
            }
            if($value>146.25 && $value<=168.75){
                return "JJV";
            }
            if($value>168.75 && $value<=191.25){
                return "J";
            }
            if($value>191.25 && $value<=213.75){
                return "JJZ";
            }
            if($value>213.75 && $value<=236.25){
                return "JZ";
            }
            if($value>236.25 && $value<=258.75){
                return "ZJZ";
            }
            if($value>258.75 && $value<=281.25){
                return "Z";
            }
            if($value>281.25 && $value<=303.75){
                return "ZSZ";
            }
            if($value>303.75 && $value<=326.25){
                return "SZ";
            }
            if($value>326.25 && $value<=348.75){
                return "SSZ";
            }
            if($value>348.75){
                return "S";
            }
        }
        if($language == "en"){
            if($value<=11.25){
                return "N";
            }
            if($value>11.25 && $value<=33.75){
                return "NNE";
            }
            if($value>33.75 && $value<=56.25){
                return "NE";
            }
            if($value>56.25 && $value<=78.75){
                return "ENE";
            }
            if($value>78.75 && $value<=101.25){
                return "E";
            }
            if($value>101.25 && $value<=123.75){
                return "ESE";
            }
            if($value>123.75 && $value<=146.25){
                return "SE";
            }
            if($value>146.25 && $value<=168.75){
                return "SSE";
            }
            if($value>168.75 && $value<=191.25){
                return "S";
            }
            if($value>191.25 && $value<=213.75){
                return "SSW";
            }
            if($value>213.75 && $value<=236.25){
                return "SW";
            }
            if($value>236.25 && $value<=258.75){
                return "WSW";
            }
            if($value>258.75 && $value<=281.25){
                return "W";
            }
            if($value>281.25 && $value<=303.75){
                return "WNW";
            }
            if($value>303.75 && $value<=326.25){
                return "NW";
            }
            if($value>326.25 && $value<=348.75){
                return "NNW";
            }
            if($value>348.75){
                return "N";
            }
        }

	}

    function apparent($apparentT,$apparentH,$apparentW){
        $e = ($apparentH/100)*6.105*pow(2.71828, ((17.27*$apparentT)/(237.7+$apparentT)));
        $calcA = round(($apparentT + 0.33*$e-0.7*$apparentW-4),1);
        return $calcA;
    }

    function dewpoint($dewT,$dewH){
        $calcD = round(((pow(($dewH/100), 0.125))*(112+0.9*$dewT)+(0.1*$dewT)-112),1);
        return $calcD;
    }

    function convertor($n,$unit1,$unit2){
		// prepare input
		$unit1 = trim(strtolower($unit1));
		$unit2 = trim(strtolower($unit2));
		$unit1 = str_replace("/","",$unit1);
		$unit2 = str_replace("/","",$unit2);
		$unit1 = str_replace("kts","kt",$unit1);
		$unit2 = str_replace("kts","kt",$unit2);
		$unit1 = str_replace("knots","kt",$unit1);
		$unit2 = str_replace("knots","kt",$unit2);
		$unit1 = str_replace("kph","kmh",$unit1);
		$unit2 = str_replace("kph","kmh",$unit2);
		$unit1 = str_replace("mb","hpa",$unit1);
		$unit2 = str_replace("mb","hpa",$unit2);
		$unit1 = str_replace("miles","mi",$unit1);
		$unit2 = str_replace("miles","mi",$unit2);
		$unit1 = str_replace("feet","ft",$unit1);
		$unit2 = str_replace("feet","ft",$unit2);
		$unit1 = str_replace("foot","ft",$unit1);
		$unit2 = str_replace("foot","ft",$unit2);

		// return same units
		if($unit1==$unit2){
			return $n;
		}

		// temperature
		else if($unit1=="c" && $unit2=="f"){
			return $n*1.8 + 32;
		}
		else if($unit1=="f" && $unit2=="c"){
			return ($n - 32)/1.8;
		}

		// wind speed
		else if($unit1=="ms" && $unit2=="kmh"){
			return $n * 3.6;
		}
		else if($unit1=="ms" && $unit2=="mph"){
			return $n * 2.23694;
		}
		else if($unit1=="ms" && $unit2=="kt"){
			return $n * 1.943844;
		}
		else if($unit1=="kmh" && $unit2=="ms"){
			return $n / 3.6;
		}
		else if($unit1=="kmh" && $unit2=="mph"){
			return $n * 0.621371;
		}
		else if($unit1=="kmh" && $unit2=="kt"){
			return $n * 0.539957;
		}
		else if($unit1=="mph" && $unit2=="ms"){
			return $n * 0.44704;
		}
		else if($unit1=="mph" && $unit2=="kmh"){
			return $n * 1.609344;
		}
		else if($unit1=="mph" && $unit2=="kt"){
			return $n * 0.868976;
		}
		else if($unit1=="kt" && $unit2=="ms"){
			return $n * 0.514444;
		}
		else if($unit1=="kt" && $unit2=="kmh"){
			return $n * 1.852;
		}
		else if($unit1=="kt" && $unit2=="mph"){
			return $n * 1.150779;
		}

		// pressure
		else if($unit1=="hpa" && $unit2=="inhg"){
			return $n * 0.02952998;
		}
		else if($unit1=="hpa" && $unit2=="mmhg"){
			return $n * 0.750063755;
		}
		else if($unit1=="inhg" && $unit2=="hpa"){
			return $n * 33.863881;
		}
		else if($unit1=="inhg" && $unit2=="mmhg"){
			return $n * 25.400069;
		}
		else if($unit1=="mmhg" && $unit2=="hpa"){
			return $n * 1.3332239;
		}
		else if($unit1=="mmhg" && $unit2=="inhg"){
			return $n * 0.03937;
		}

		// precipitation
		else if($unit1=="mm" && $unit2=="in"){
			return $n * 0.0393701;
		}
		else if($unit1=="in" && $unit2=="mm"){
			return $n * 25.4;
		}

		else if($unit1=="mm" && $unit2=="cm"){
			return $n * 0.1;
		}
		else if($unit1=="cm" && $unit2=="mm"){
			return $n * 10;
		}

	}
?>
