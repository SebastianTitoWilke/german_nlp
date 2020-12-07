<?php

	ini_set('memory_limit', '1500M');
	include_once('Satzwerkzeug.php');
	include_once('nlp_array.php');
	if (!isset($_SESSION)) {session_start();}
	//exit(var_dump($nlp_array));
	function posTagger($input) {
		//$nlp_base = file_get_contents('DeReKo-2014-II-MainArchive-STT.100000.freq');
		//$nlp_array = explode('br', nl2br($nlp_base));
		global $nlp_array;
		
		$worte = explode(' ', $input);	
		$nlp_wort = array();
		/*
		$file = fopen('nlp_array.php', 'wb');
		foreach($nlp_array as $grammatik_zeile) {
			$zeile = preg_split("/[\s]+/", $grammatik_zeile);
			if(isset($zeile[2]) && isset($zeile[3]) && isset($zeile[4])) {
				fwrite($file, '"'.str_replace('"', '\"', str_replace(array("\r\n", "\r", "\n", " />"), '', str_replace('<', '', $grammatik_zeile))).'",'."\n");
			}
		} */
		
		foreach($worte as $wort) {
			$wort = str_replace(',', '',$wort);	
			$grammatik = array();
			$pos_summe = array();
			foreach($nlp_array as $grammatik_zeile) {		
				$zeile = preg_split("/[\s]+/", $grammatik_zeile);
				//exit(var_dump($zeile));
				if(isset($zeile[0]) && isset($zeile[1]) && isset($zeile[2])) {
					$wort_der_zeile = $zeile[0];
					//exit($wort_der_zeile);
					$wort_grundform = $zeile[1];
					$grammatik_der_zeile = $zeile[2];
					//if(strcasecmp($wort, $wort_der_zeile) == 0) {				
					if(trim($wort) == trim($wort_der_zeile)) {					
						if(preg_match('/(NN|NE)/', $grammatik_der_zeile)) {
							$grammatik[] = array( 'POS' => $grammatik_der_zeile, 'Grundform'  => $wort_grundform, 'Nomen' => true, 'Verb' => false, 'Adjektiv' => false, 'Interrogation' => array('Wer', 'Was', 'Wen'));
						} elseif(preg_match('/(VVFIN|VAFIN|VMFIN|VVPP|VAPP|VMPP)/', $grammatik_der_zeile))  {
							$grammatik[] = array( 'POS' => $grammatik_der_zeile, 'Grundform'  => $wort_grundform, 'Nomen' => false, 'Verb' => true, 'Adjektiv' => false, 'Interrogation' => array('Was '.$wort_der_zeile));
						} elseif(preg_match('/(ADJA|ADJD)/', $grammatik_der_zeile)) {
							$grammatik[] = array( 'POS' => $grammatik_der_zeile, 'Grundform'  => $wort_grundform, 'Nomen' => false, 'Verb' => false, 'Adjektiv' => true, 'Interrogation' => array('Wie'));
						} else { 
							$grammatik[] = array( 'POS' => $grammatik_der_zeile, 'Grundform'  => $wort_grundform, 'Nomen' => false, 'Verb' => false, 'Adjektiv' => false, 'Interrogation' => array());
						}
						//exit(var_dump($grammatik));
					} 
				}
			} 
			foreach($grammatik as $pos) {
				$pos_summe[] = $pos['POS'];
			}
			$nlp_wort[] = array( 'Wort' => $wort, 'Grammatik' => $grammatik, 'POS_Summe' => $pos_summe);
		}
		
		if(empty($nlp_wort[0]['Grammatik'])) {
			$nlp_wort[0]['Grammatik'] = array(  
				0 => array( 'POS' => 'VVFIN', 'Grundform'  => ''),
				1 => array( 'POS' => 'VAFIN', 'Grundform'  => ''),
				2 => array( 'POS' => 'VMFIN', 'Grundform'  => ''),
				3 => array( 'POS' => 'VVINF', 'Grundform'  => ''),
				4 => array( 'POS' => 'VAINF', 'Grundform'  => ''),
				5 => array( 'POS' => 'VMINF', 'Grundform'  => ''),
				6 => array( 'POS' => 'VVIMP', 'Grundform'  => ''),
				7 => array( 'POS' => 'VAIMP', 'Grundform'  => ''),
				8 => array( 'POS' => 'VVPP', 'Grundform'  => ''),
				9 => array( 'POS' => 'VAPP', 'Grundform'  => ''),
				10 => array( 'POS' => 'VMPP', 'Grundform'  => ''),
				11 => array( 'POS' => 'VVIZU ', 'Grundform'  => ''),
				12 => array( 'POS' => 'ADJA', 'Grundform'  => ''),
				13 => array( 'POS' => 'ADJD', 'Grundform'  => ''),
				14 => array( 'POS' => 'NN', 'Grundform'  => ''), 
				15 => array( 'POS' => 'NE', 'Grundform'  => '')
			);	
		}
		
		for($i = 1; $i < count($nlp_wort); $i++) {
			if(empty($nlp_wort[$i]['Grammatik'])) {
				if($nlp_wort[$i]['Wort'] != strtolower($nlp_wort[$i]['Wort'])) {	
					$nlp_wort[$i]['Grammatik'] = array(0 => array( 'POS' => 'NN', 'Grundform'  => ''), 1 => array( 'POS' => 'NE', 'Grundform'  => ''));
				} else {
					$nlp_wort[$i]['Grammatik'] = array(  
						0 => array( 'POS' => 'VVFIN', 'Grundform'  => ''),
						1 => array( 'POS' => 'VAFIN', 'Grundform'  => ''),
						2 => array( 'POS' => 'VMFIN', 'Grundform'  => ''),
						3 => array( 'POS' => 'VVINF', 'Grundform'  => ''),
						4 => array( 'POS' => 'VAINF', 'Grundform'  => ''),
						5 => array( 'POS' => 'VMINF', 'Grundform'  => ''),
						6 => array( 'POS' => 'VVIMP', 'Grundform'  => ''),
						7 => array( 'POS' => 'VAIMP', 'Grundform'  => ''),
						8 => array( 'POS' => 'VVPP', 'Grundform'  => ''),
						9 => array( 'POS' => 'VAPP', 'Grundform'  => ''),
						10 => array( 'POS' => 'VMPP', 'Grundform'  => ''),
						11 => array( 'POS' => 'VVIZU ', 'Grundform'  => ''),
						12 => array( 'POS' => 'ADJA', 'Grundform'  => ''),
						13 => array( 'POS' => 'ADJD', 'Grundform'  => '')
					);
				}
			} 		
		}

		return $nlp_wort; 
	}	
	
	function satztypen($postagger_antwort) {
		
		$keineFrage = false;
		$keinImperativ = false;	
		$satztyp = array();
		if(isset($postagger_antwort[0]['Grammatik'][0]['POS']) 
			//&& count($postagger_antwort[0]['Grammatik']) == 1 
			&& preg_grep ('/(PWS|PWAT|PWAV|VVFIN|VAFIN|VMFIN)/', $postagger_antwort[0]['POS_Summe'])
			) {
			$satztyp = 'Interrogativsatz';
		} 
		
		for($i = 1; $i < count($postagger_antwort)-1; $i++) {
			if(isset($postagger_antwort[$i]['Grammatik'][0]['POS']) 
				//&& count($postagger_antwort[$i]['Grammatik']) == 1 
				&& preg_grep ('/(PWS|PWAT|PWAV)/', $postagger_antwort[$i]['POS_Summe'])
				&& isset($postagger_antwort[$i+1]['Grammatik'][0]['POS']) 
				//&& count($postagger_antwort[$i+1]['Grammatik']) == 1 
				&& preg_grep ('/(VVFIN|VAFIN|VMFIN)/', $postagger_antwort[$i+1]['POS_Summe'])			
				) {
				$satztyp = 'Interrogativsatz';			
			}
		}
		
		if($satztyp != 'Interrogativsatz') {
			$keineFrage = true;			
		}
		
		if(isset($postagger_antwort[0]['Grammatik'][0]['POS']) 
			&& count($postagger_antwort[0]['Grammatik']) == 1 
			&& preg_match('/(VVIMP|VAIMP)/', $postagger_antwort[0]['Grammatik'][0]['POS'])
			) {
			$satztyp = 'Imperativsatz';
		} else {
			$keinImperativ = true;
		}	
					
		
		if($keineFrage && $keinImperativ) {
			$satztyp = 'Deklarativsatz';
		}
				
		return $satztyp;  
	}
	
	$satzWerkzeug = new Satzwerkzeug();
	$input = $_REQUEST['q'];
	$satztyp = satztypen(posTagger($input));
	if($satztyp == 'Interrogativsatz') {
		$_SESSION['Satzspeicher'][] = array('ip'=> $_SERVER['REMOTE_ADDR'], 'time'=> time(), 'Satz'=> $input, 'nlp'=> posTagger($input), 'Interrogationen' => array());
		echo '<pre>'.var_export($satzWerkzeug-> antworte($input), true).'</pre>';
	} 
	
	if($satztyp == 'Deklarativsatz') {
		$_SESSION['Satzspeicher'][] = array('ip'=> $_SERVER['REMOTE_ADDR'], 'time'=> time(), 'Satz'=> $input, 'nlp'=> posTagger($input), 'Interrogationen' => $satzWerkzeug->FragenAntwortExtraktion(posTagger($input)));
		echo '<pre>'.var_export($satzWerkzeug->FragenAntwortExtraktion(posTagger($input)), true).'</pre>';
	}
		
	//echo '<pre>'.var_export(posTagger($input), true).'</pre>';
	//session_destroy();
	echo '<pre>'.var_export($_SESSION['Satzspeicher'], true).'</pre>';
	//unset($_SESSION);
	
	if(isset($_REQUEST['refresh']) && $_REQUEST['refresh'] == 1) {
	  session_destroy();
	}		
?>
