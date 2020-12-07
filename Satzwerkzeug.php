<?php

require_once('dativ_verben_array.php');

class Satzwerkzeug {
    
	/*function antworteAufDieFrage($pos_array) {
		global $_SESSION;
		$antworten = array();
		foreach($_SESSION['Satzspeicher'] as $satz) {
			$reg = '';
			$cnt = 0;
			foreach($pos_array as $nlp) {
				$cnt++;
				if($cnt > 1) {
					$reg .= '(?=.*'.$nlp['Wort'].').*';
				}
			}
			$reg = substr($reg, 0, -2);
			//exit($reg);
			foreach($satz['nlp'] as $nlp) {
				foreach($nlp['Grammatik'] as $nlp_grammatik) {									 
					if(!empty($nlp_grammatik['Interrogation']) && in_array($pos_array[0]['Wort'], $nlp_grammatik['Interrogation']) && preg_match('/'.$reg.'/', $satz['Satz'])){
						$antworten[] = $nlp['Wort'];
					}						
					if(!empty($nlp_grammatik['Interrogation']) && $pos_array[1]['Grammatik'][0]['Verb'] && in_array($pos_array[0]['Wort']." ".$pos_array[0]['Wort'], $nlp_grammatik['Interrogation']) && preg_match('/'.$reg.'/', $satz['Satz'])){
						$antworten[] = $nlp['Wort'];
					}						
				}
			}

		}
		
		return array_unique($antworten);
	}*/
	
	function antworte($input) {
		global $_SESSION;
		$Antwort = array();
		foreach($_SESSION['Satzspeicher'] as $satz) {
			//exit(var_dump($satz));
			if(!empty($satz['Interrogationen'])) {
				//exit('not empty');
				foreach($satz['Interrogationen'] as $Fragen_und_Antworten) {
					//exit('into');
					//exit(var_dump(array($Fragen_und_Antworten['Frage'], $input)));					
					if(strtolower($Fragen_und_Antworten['Frage']) == strtolower($input)) {
						$Antwort[] = $Fragen_und_Antworten['Antwort'];
					}
				}
			}
		}		
		return array_unique($Antwort);		
	}
	
	function verarbeiteDieAussage($pos_array) {
		echo '<pre>'.var_export(Satzwerkzeug::verbenIndizis($pos_array), true).'</pre>';
	}
	
	function verbenIndizis($pos_array){
		//Fragewörter füllen, wenn möglich
		$verb_indizis = array();
		foreach($pos_array as $zeilen_schluessel => $pos_zeile) {
			foreach($pos_zeile['Grammatik'] as $key => $pos) {
				if(preg_match('/(VVFIN|VAFIN|VMFIN|VVPP|VAPP|VMPP)/', $pos['POS'])) {
					$verb_indizis[] = array('Index' => $zeilen_schluessel, 'Wort' => $pos_zeile['Wort'], 'Grammatik' => $pos['POS']);		
				}
			}
		}
		
		return $verb_indizis;
	}
	
	function NomenIndizis($pos_array){
		//Fragewörter füllen, wenn möglich
		$nomen_indizis = array();
		foreach($pos_array as $zeilen_schluessel => $pos_zeile) {
			foreach($pos_zeile['Grammatik'] as $key => $pos) {
				if(preg_match('/(NN|NE)/', $pos['POS'])) {
					$nomen_indizis[] = array('Index' => $zeilen_schluessel, 'Wort' => $pos_zeile['Wort'], 'Grammatik' => $pos['POS']);		
				}
			}
		}
		
		return $nomen_indizis;
	}	
	
	function SortiereSatzInhaltNachNomenUndVerben($pos_array) {
		$verben_indizis = Satzwerkzeug::verbenIndizis($pos_array);
		$satzteile_array = array();
		foreach($verben_indizis as $verben_index) {
			$verb_index = $verben_index['Index'];
			for($i = 0; $i < count($pos_array); $i++) {
				if($i != $verb_index) {
					$nomen_indizis = Satzwerkzeug::NomenIndizis($pos_array);
					$ist_ein_nomen = false;
					foreach($nomen_indizis as $nomen_index) {
						if($nomen_index['Index'] = $i) {
							$ist_ein_nomen = true;
						}
					}						
					$satzteile_array[] = array('Verb' => false, 'Nomen' => $ist_ein_nomen, $pos_array[$i]);
				} else {
					$satzteile_array[] = array('Verb' => true, 'Nomen' => false, $pos_array[$i]);
				}
			}
		}
		return $satzteile_array;
	}
	
	function FragenAntwortExtraktion($pos_array) {		
	
		for($i=0; $i < count($pos_array); $i++) {
			foreach($pos_array[$i]['Grammatik'] as $nlp) {				
				if($nlp['Verb']) {
					$schluesselwort[] = array( 'Wort' => $pos_array[$i]['Wort'], 'Index' => $i, 'Typ' => 'Verb');
				} elseif($nlp['Nomen']){
					$schluesselwort[] = array( 'Wort' => $pos_array[$i]['Wort'], 'Index' => $i, 'Typ' => 'Nomen');
				} elseif($nlp['Adjektiv']) {
					$schluesselwort[] = array( 'Wort' => $pos_array[$i]['Wort'], 'Index' => $i, 'Typ' => 'Adjektiv');
				}
			}
		}
		$verb_indizis = array();
		foreach($schluesselwort as $schluessel) {
			if($schluessel['Typ'] == 'Verb') {
				$verb_indizis[] = $schluessel['Index'];
			}
		}
		//exit(var_dump($verb_indizis));
		$Frage_Antwort = array();
		if(count($verb_indizis) == 1) {
			$Frage_Antwort = Satzwerkzeug::FragenAntwortExtraktion_EinVerbImSatz($pos_array, $verb_indizis);
		}
		
		return $Frage_Antwort;
	}
	
	function IstEinDativImSatz($pos_array) {
		//es fehlt, der Daitv ohne Sonderwort, Beispiel: Karl gibt Anna (Wem gibt Anna), oder: Die Stunde schlägt Otto (Wem schlägt die Stunde) 
		$EnthaeltDativ = false;
		$fcnt = -1;
		foreach($pos_array as $pos) {			
			$fcnt++;
			if(preg_match('/(dem|mit|ihm|ihr|ihrem|vom|von)/',$pos['Wort'])) {
				$EnthaeltDativ = true;	
				return array('wahrheitswert' => $EnthaeltDativ, 'Dativ-Index' => $fcnt, 'Dativ-Details' => $pos_array[$fcnt]);
			}
		}
		
		global $dativ_verben;
		global $dativ_verben_mit_folgendem_akkusativ;
		global $dativ_verben_mit_folgender_praeposition;
		
		foreach($dativ_verben as $verb_info) {			
			$fcnt = -1;
			foreach($pos_array as $pos) {			
				$fcnt++;
				if(preg_match('/'.$verb_info['Wurzel'].'/',$pos['Wort'])) {
					$EnthaeltDativ = true;	
					return array('wahrheitswert' => $EnthaeltDativ, 'Dativ-Index' => $fcnt, 'Dativ-Details' => $pos_array[$fcnt], 'Zusatz' => false);
				}
			}		
		}
		
		foreach($dativ_verben_mit_folgendem_akkusativ as $verb_info) {
			$fcnt = -1;
			foreach($pos_array as $pos) {			
				$fcnt++;
				if(preg_match('/'.$verb_info['Wurzel'].'/',$pos['Wort'])) {
					$EnthaeltDativ = true;	
					return array('wahrheitswert' => $EnthaeltDativ, 'Dativ-Index' => $fcnt, 'Dativ-Details' => $pos_array[$fcnt], 'Zusatz' => 'Akkusativ');
				}
			}
		}
		
		$fcnt = -1;
		foreach($dativ_verben_mit_folgender_praeposition as $verb_info) {			
			$fcnt = -1;		
			foreach($pos_array as $pos) {			
				$fcnt++;
				if(preg_match('/'.$verb_info['Wurzel'].'/',$pos['Wort'])) {
					$EnthaeltDativ = true;	
					return array('wahrheitswert' => $EnthaeltDativ, 'Dativ-Index' => $fcnt, 'Dativ-Details' => $pos_array[$fcnt], 'Zusatz' => 'Praeposition', 'Praeposition' => $verb_info['Praeposition']);
				}
			}		
		}
				
		if(!$EnthaeltDativ) {
			$fcnt = -1;
			return array('wahrheitswert' => $EnthaeltDativ);
		} 
		
		
		
		
	}
	
	function IstEinGenitivImSatz($pos_array) {
		$EnthaeltGenitiv = false;
		$fcnt = -1;
		foreach($pos_array as $pos) {			
			$fcnt++;
			if(preg_match('/(von|des|meines|meiner|meine|mein|deiner|deines|deine|dein)/',$pos['Wort'])) {
				$EnthaeltGenitiv = true;	
				return array('wahrheitswert' => $EnthaeltGenitiv, 'Genitiv-Index' => $fcnt, 'Genitiv-Details' => $pos_array[$fcnt]);
			}
		}
		if(!$EnthaeltGenitiv) {
			$fcnt = -1;
			return array('wahrheitswert' => $EnthaeltGenitiv);
		} 
	}	
	
	
	function FindeNaechstesNomenNachIndex($pos_array, $index) {
		for($i = $index+1; $i < count($pos_array); $i++) {
			foreach($pos_array[$i]['Grammatik'] as $nlp) {
				if(preg_match('/(NN|NE)/', $nlp['POS'])) {
					return array('Index' => $i, 'nlp' => $pos_array[$i]);
				}
			}
		}
		return false;
	}

	
	function PositionenDerNegationen($pos_array) {
		$negationen = array();
		for($i = 0; $i < count($pos_array); $i++) {
			foreach($pos_array[$i]['Grammatik'] as $nlp) {
				if(preg_match('/(PIAT|PIS)/', $nlp['POS'])) {
					array_push($negationen, array('Index' => $i, 'nlp' => $pos_array[$i]));
				}
			}
		}
		return $negationen;
	}		
	
	function PositionenDerUndVerknuepfungen($pos_array) {
		$Und_KON = array();
		for($i = 0; $i < count($pos_array); $i++) {
			foreach($pos_array[$i]['Grammatik'] as $nlp) {
				if(preg_match('/(KON)/', $nlp['POS']) && !preg_match('/(oder)/', $nlp['Grundform'])) {
					array_push($Und_KON, array('Index' => $i, 'nlp' => $pos_array[$i]));
				}
			}
		}
		return $Und_KON;
	}				

	function PositionenDerOderVerknuepfungen($pos_array) {
		$Oder_KON = array();
		for($i = 0; $i < count($pos_array); $i++) {
			foreach($pos_array[$i]['Grammatik'] as $nlp) {
				if(preg_match('/(KON)/', $nlp['POS']) && !preg_match('/(und)/', $nlp['Grundform'])) {
					array_push($Oder_KON, array('Index' => $i, 'nlp' => $pos_array[$i]));
				}
			}
		}
		return $Oder_KON;
	}			
	
    function FragenAntwortExtraktion_EinVerbImSatz($pos_array, $verb_indizis) {
		$Satz_vor_Verb = '';
		$Satz_nach_Verb = '';
		$Verb = '';
		$verb_erreicht = false;		
		//if kein Dativ; Genitiv; keine 1.,2.,4., 5. Person, keine Aussage zur Zeit oder Ort
		//exit(var_dump(Satzwerkzeug::IstEinDativImSatz($pos_array)));
		foreach($verb_indizis as $index) {			
			for($i=0; $i < count($pos_array); $i++) {
				if($i != $index) {
					if(!$verb_erreicht) {
						$Satz_vor_Verb .= $pos_array[$i]['Wort'].' ';
					} else {
						$Satz_nach_Verb .= $pos_array[$i]['Wort'].' ';
					}
				} else {
					$verb_erreicht = true;
					$Verb .= $pos_array[$i]['Wort'].' ';				
				}
			}
		}
		
		$Genetiv_Objekt = '';
		$Genitivattribut = '';
		$genetiv = Satzwerkzeug::IstEinGenitivImSatz($pos_array);
		if($genetiv['wahrheitswert']) {
			$Genetiv_Objekt .= $pos_array[$genetiv['Genitiv-Index']-1]['Wort'];
			for($i = $genetiv['Genitiv-Index']; $i < count($pos_array); $i++) {
				$Genitivattribut .= $pos_array[$i]['Wort'].' ';
			}
		}
		//exit(var_dump(array( 'Objekt' => $Genetiv_Objekt, 'Genetivattribut' => trim($Genitivattribut))));
			
			
		//return array('Satz vor Verb' => trim($Satz_vor_Verb), 'Satz nach Verb' => trim($Satz_nach_Verb), 'Verb' => trim($Verb));
		$Frage_Antwort[] = array('Frage' => 'Wer '.trim($Verb).' '.trim($Satz_nach_Verb), 'Antwort' => trim($Satz_vor_Verb));
		$Frage_Antwort[] = array('Frage' => 'Was '.trim($Verb).' '.trim($Satz_nach_Verb), 'Antwort' => trim($Satz_vor_Verb));
		$Frage_Antwort[] = array('Frage' => 'Wer '.trim($Verb), 'Antwort' => trim($Satz_vor_Verb));
		$Frage_Antwort[] = array('Frage' => 'Was '.trim($Verb), 'Antwort' => trim($Satz_vor_Verb));		
		$Frage_Antwort[] = array('Frage' => 'Wer '.trim($Verb).' '.lcfirst(trim($Satz_vor_Verb)), 'Antwort' => trim($Satz_nach_Verb));
		$Frage_Antwort[] = array('Frage' => 'Wer wird (ge|be)('.trim($pos_array[$verb_indizis[0]]['Grammatik'][0]['Grundform']).'|'.trim(trim($Verb)).')', 'Antwort' => trim($Satz_nach_Verb));
		$Frage_Antwort[] = array('Frage' => 'Was '.trim($Verb).' '.lcfirst(trim($Satz_vor_Verb)), 'Antwort' => trim($Satz_nach_Verb));
		$Frage_Antwort[] = array('Frage' => 'Wen '.trim($Verb).' '.lcfirst(trim($Satz_vor_Verb)), 'Antwort' => trim($Satz_nach_Verb));
		$Frage_Antwort[] = array('Frage' => 'Wem '.trim($Verb).' '.lcfirst(trim($Satz_vor_Verb)), 'Antwort' => trim($Satz_nach_Verb));
		//$Frage_Antwort[] = array('Frage' => 'Wem '.trim($Verb).' '.lcfirst(trim($Satz_vor_Verb)).' '.trim($Satz_nach_Verb), 'Antwort' => trim($Satz_nach_Verb));		
		$Frage_Antwort[] = array('Frage' => 'Wie '.trim($Verb).' '.lcfirst(trim($Satz_vor_Verb)), 'Antwort' => trim($Satz_nach_Verb));		
		$Frage_Antwort[] = array('Frage' => 'Wohin '.trim($Verb).' '.lcfirst(trim($Satz_vor_Verb)), 'Antwort' => trim($Satz_nach_Verb));
		$Frage_Antwort[] = array('Frage' => 'Woher '.trim($Verb).' '.lcfirst(trim($Satz_vor_Verb)), 'Antwort' => trim($Satz_nach_Verb));
		$Frage_Antwort[] = array('Frage' => 'Wann '.trim($Verb).' '.lcfirst(trim($Satz_vor_Verb)), 'Antwort' => trim($Satz_nach_Verb));
		$Frage_Antwort[] = array('Frage' => 'Wann '.trim($Verb).' '.trim($Satz_nach_Verb), 'Antwort' => trim($Satz_vor_Verb));	
		$Frage_Antwort[] = array('Frage' => 'Wessen '.trim($Genetiv_Objekt).' '.trim($Verb).' '.lcfirst(trim($Satz_vor_Verb)), 'Antwort' => trim($Genitivattribut));	
		$Frage_Antwort[] = array('Frage' => 'Was macht '.trim($Satz_vor_Verb), 'Antwort' => trim($Satz_vor_Verb).' '.trim($Verb).' '.trim($Satz_nach_Verb));	
		
		return $Frage_Antwort;
	}		
	

}


?>


