<?php


ini_set('memory_limit', '1500M');
	
include_once('Grammatik.php');
include_once('Frageworte.php');
include_once('Memory.php');

function NLP_Extraktion($input) {
	global $Grammatik;	
	$worte = explode(' ', $input);	
	$nlp_wort = array();
	$nlp_string = '';
	foreach($worte as $wort) {
		$wort = str_replace(',', '',$wort);	
		$grammatik_2 = array();
		$pos_summe = array();
		foreach($Grammatik as $grammatik_zeile) {		
				$wort_der_zeile = $grammatik_zeile['Begriff'];
				$wort_grundform = $grammatik_zeile['Grundform'];
				$grammatik_der_zeile = $grammatik_zeile['POS'];
				//if(strcasecmp($wort, $wort_der_zeile) == 0) {				
				if(trim($wort) == trim($wort_der_zeile)) {					
					$grammatik_2[] = array( 'POS' => $grammatik_der_zeile, 
													'Grundform'  => $wort_grundform); 
				} 
		} 
		$nlp_string .= '<'; 
		foreach($grammatik_2 as $pos) {
			$pos_summe[] = $pos['POS'];
			$nlp_string .= $pos['POS'].'|';
		}
		if(count($pos_summe) == 0) {
			$nlp_string  .=  'ERROR ';
		}
		$nlp_string  =  substr($nlp_string, 0, -1).'> '; 		
		$nlp_wort[] = array( 'Wort' => $wort, 'Grammatik' => $grammatik_2, 'POS_Summe' => $pos_summe);
	}
	return array('UserIP' =>  $_SERVER['REMOTE_ADDR'], 'Satz'=> $input, 'Satztyp' => Satztyp($nlp_string) ,'nlp_time' => time(), 'nlp_string' => substr($nlp_string, 0,-1),'nlp_array' => $nlp_wort);
}


function VerbenSplitter($nlp) {
	$nlp_string = $nlp['nlp_string'];
	$nlp_array = $nlp['nlp_array'];
	$nlp_time = $nlp['nlp_time'];	
	$inhalte = array();
    $grammatik = explode(' ',$nlp_string);
	$praeverbal = '';
	$postverbal = '';
	$beinhaltet_verb = false;
	$verb = '';
	if(!preg_match('/(KOUI|KOUS)/', $nlp_string)) {
		for($i = 0; $i < count($nlp_array); $i++) { 
			if(preg_match('/(VMFIN|VAFIN|VVFIN|VVFIN)/', $grammatik[$i])) {
				$beinhaltet_verb = true;
				$verb .= $nlp_array[$i]['Wort']." ";
			}
			 if($beinhaltet_verb == false) {
				$praeverbal .= $nlp_array[$i]['Wort']." ";
			 }				
			if(!preg_match('/(VMFIN|VAFIN|VVFIN|VVFIN)/', $grammatik[$i]) && $beinhaltet_verb == true) {
				$postverbal .=  $nlp_array[$i]['Wort']." ";
			}
			if($i == (count($nlp_array)-1)) {
				$inhalte[] = array('praeverbal' => trim($praeverbal), 'postverbal' => trim($postverbal), 'verb' => trim($verb));
			}	
		}
	} 
	//exit(var_dump($inhalte));
	
	return $inhalte;	
}

function AussagenSpider($nlp) {
	$inhalte = array();
	global $Frageworte;
	$nlp_string = $nlp['nlp_string'];
	$nlp_array = $nlp['nlp_array'];
	$nlp_time = $nlp['nlp_time'];
    $memory = array();	
	foreach($Frageworte as $fragewort) {
		//hier müssen die Satzumformungen angepasst werden, um das Array zu füllen
		$praeverbal = '';
		$postverbal = '';
		$beinhaltet_verb = false;
		$verb = '';
		if(Satztyp($nlp_string) == 'Aussage') {			
			$info = VerbenSplitter($nlp);
			$verb = $info[0]['verb'];
			$postverbal = $info[0]['postverbal'];
			$praeverbal = $info[0]['praeverbal'];
			if(preg_match('/(Wer)/',$fragewort['Fragewort'])) {		
				foreach(Wer($nlp, $nlp_string, $verb, $postverbal, $praeverbal, $fragewort) as $inhalt) {
					array_push($memory, $inhalt);								
				}
			}
			if(preg_match('/(Was)/',$fragewort['Fragewort'])) {		
				foreach(Was($nlp, $nlp_string, $verb, $postverbal, $praeverbal,$fragewort) as $inhalt) {
					array_push($memory, $inhalt);								
				}
			}			
			if(preg_match('/(Wessen)/',$fragewort['Fragewort'])) {		
				foreach(Wessen($nlp, $nlp_string, $verb, $postverbal, $praeverbal, $fragewort) as $inhalt) {
					array_push($memory, $inhalt);								
				}
			}			
			
			/*
			$memory[] = array('Frage' => trim(trim($fragewort['Fragewort']).' '.$verb.' '.$postverbal), 'Antwort' => $praeverbal);
			$memory[] = array('Frage' => trim(trim($fragewort['Fragewort']).' '.$postverbal.' '.$verb), 'Antwort' => $praeverbal);
			$memory[] = array('Frage' => trim(trim($fragewort['Fragewort']).' '.$verb.' '.$praeverbal), 'Antwort' => $postverbal);
			$memory[] = array('Frage' => trim(trim($fragewort['Fragewort']).' '.$praeverbal.' '.$verb), 'Antwort' => $postverbal);

			if(preg_match('/\s/',$praeverbal)) {
				$all_preaverbal = explode(' ', $praeverbal);
			}
			if(preg_match('/\s/',$postverbal)) {
				$all_postverbal = explode(' ', $postverbal);
			}			 
			$pre_kombinationen = AlleBegriffsAnordnungenEinesObjects($all_preaverbal);
			//exit('<pre>'.print_r($postverbal, true).'</pre>');

			$post_kombinationen = AlleBegriffsAnordnungenEinesObjects($all_postverbal);
			foreach($pre_kombinationen as $pre_kombis) {
				foreach($post_kombinationen as $post_kombis) {
					$memory[] = array('Frage' => trim(trim($fragewort['Fragewort']).' '.$verb.' '.$pre_kombis), 'Antwort' => $post_kombis);
					$memory[] = array('Frage' => trim(trim($fragewort['Fragewort']).' '.$post_kombis.' '.$verb), 'Antwort' => $pre_kombis);
					$memory[] = array('Frage' => trim(trim($fragewort['Fragewort']).' '.$verb.' '.$post_kombis), 'Antwort' => $pre_kombis);
					$memory[] = array('Frage' => trim(trim($fragewort['Fragewort']).' '.$pre_kombis.' '.$verb), 'Antwort' => $post_kombis);					
				}
			}				
			//exit('<pre>'.print_r($pre_kombinationen, true).'</pre>');
			/*if(!preg_match('/(VMFIN|VAFIN|VVFIN|VVFIN).*(NN|NE)/',$nlp_string) 
				&& preg_match('/(VMFIN|VAFIN|VVFIN|VVFIN).*(ADJD)/',$nlp_string)
				&& preg_match('/(Was)/',$fragewort['Fragewort'])
				&& !preg_match('/(für)/',$fragewort['Fragewort'])) {			
				$memory[] = array('Frage' => trim(trim($fragewort['Fragewort']).' '.$verb.' '.$praeverbal), 'Antwort' => $postverbal);								
			}*/
		}
	}
	exit('<pre>'.print_r($memory, true).'</pre>');
	return $memory;
}



function array_speichern($input, $memory) {
	array_push($memory, $input);
	file_put_contents('Memory.php', '<?php $memory=' .var_export($memory, true).'?>');		
}

function antworten($nlp, $memory) {
	$nlp_string = $nlp['nlp_string'];
	$nlp_array = $nlp['nlp_array'];
	$nlp_time = $nlp['nlp_time'];
	$nlp_satz = $nlp['Satz'];
	$Antwort = '';
	if(Satztyp($nlp_string) == 'Frage') {			
		foreach($memory as $web) {
			foreach($web as $knoten) {
				if(preg_match('/'.$nlp_satz.'/i', $knoten['Frage'])) {
					$Antwort = $knoten['Antwort'];
				}								
			}
		}	
	}
	return $Antwort;

}

function Inhalts_Extraktion($nlp) {
	$inhalte = array();
	global $Frageworte;
	$nlp_string = $nlp['nlp_string'];
	$nlp_array = $nlp['nlp_array'];
	$nlp_time = $nlp['nlp_time'];			
	foreach($Frageworte as $fragewort) {
		//hier müssen die Satzumformungen angepasst werden, um das Array zu füllen
		$praeverbal = '';
		$postverbal = '';
		$beinhaltet_verb = false;
		$verb = '';
		if(Satztyp($nlp_string) == 'Aussage') {			
			if(trim($fragewort['Fragewort']) == 'Was') {
				return was($nlp);
			}
			if(trim($fragewort['Fragewort']) == 'Wer') {
				//wer($nlp);
			}
		}
	}
	return array('inhalte' => $inhalte, 'Details' => array('praeverbal' => $praeverbal, 'verb' => $verb, 'postverbal' => $postverbal));
}

function Wer($nlp, $nlp_string, $verb, $postverbal, $praeverbal, $fragewort) {
	$Inhalte = array();
	$Inhalte[] = array('Frage' => 'Wer '.$verb.' '.$postverbal , 'Antwort' => trim($praeverbal));
	return $Inhalte;
}

function Was($nlp, $nlp_string, $verb, $postverbal, $praeverbal, $fragewort) {
	$Inhalte = array();
	if($fragewort['Grammatik'] == 'Nominativ' && !preg_match('/(VMFIN|VAFIN|VVFIN).*(NN|NE|PPER).*(ART).*(NN|NE|PPER)/',$nlp_string)) {
		$Inhalte[] = array('Frage' => 'Was '.$verb.' '.$praeverbal , 'Antwort' => trim($postverbal));
	}
	if($fragewort['Grammatik'] == 'Nominativ' && preg_match('/(VMFIN|VAFIN|VVFIN).*(NN|NE|PPER).*(ART).*(NN|NE|PPER)/',$nlp_string)) {
		$Inhalte[] = array('Frage' => 'Was '.$verb.' '.$praeverbal , 'Antwort' => trim($postverbal));
	}	
	return $Inhalte;
}

function Wessen($nlp, $nlp_string, $verb, $postverbal, $praeverbal, $fragewort) {
	$Inhalte = array();
    $full_objects = get_full_Objects($nlp);
	//exit('<pre>'.print_r($full_objects, true).'</pre>');
	for($i = 0; $i < count($full_objects); $i++) {
		if(check_if_Object_is_Genetiv($full_objects[$i], $nlp) && $full_objects[$i]['Ordnung'] == 'postverb') {
			$Inhalte[] = array('Frage' => 'Wessen '.entferne_Artikel($full_objects[$i-1]['Satzglied']), 'Antwort' => trim($full_objects[$i]['Satzglied']));			
			$Inhalte[] = array('Frage' => 'Wessen '.entferne_Artikel($full_objects[$i-1]['Satzglied']).' '.$verb.' '.$praeverbal, 'Antwort' => trim($full_objects[$i-1]['Satzglied'].' '.$full_objects[$i]['Satzglied']));
		} 
		if(check_if_Object_is_Genetiv($full_objects[$i], $nlp) && $full_objects[$i]['Ordnung'] == 'paeverb') {
			$Inhalte[] = array('Frage' => 'Wessen '.entferne_Artikel($full_objects[$i-1]['Satzglied']), 'Antwort' => trim($full_objects[$i]['Satzglied']));			
			$Inhalte[] = array('Frage' => 'Wessen '.entferne_Artikel($full_objects[$i-1]['Satzglied']).' '.$verb.' '.$praeverbal, 'Antwort' => trim($full_objects[$i-1]['Satzglied'].' '.$full_objects[$i]['Satzglied']));
		} 
		
	}
	return $Inhalte;
}

function entferne_Artikel($Satzglied) {
	$artikel = array('/das/','/der/','/die/');
	$str = preg_replace($artikel,'', $Satzglied);
	return trim($str);
}

function check_if_Object_is_Genetiv($object, $nlp) {
	$is_genetiv = false;
	if(isset($nlp['nlp_array'][$object['start']]) && preg_match('/(von|des|seines|seiner|ihres|ihrer|meines|meiner|meine|mein|deiner|deines|deine|dein)/',$nlp['nlp_array'][$object['start']]['Wort'])) {
		$is_genetiv = true;
	}
	return $is_genetiv;
}

function get_Objects($nlp) {
	$objects = array();
	$satzposition = -1;
	foreach($nlp['nlp_array'] as $arr) {
		$is_object = false;
		$satzposition++;
		foreach($arr['POS_Summe'] as $grammatik) {
			if($grammatik == 'NN' || $grammatik == 'NE' || $grammatik == 'PPER') {
				$is_object = true; 
			}
		}
		if($is_object == true) {
			array_push($objects, array('Wort'=> $arr['Wort'], 'Satzposition' => $satzposition));
		}
	}
	return $objects;
}

function get_full_Objects($nlp) {
	$objects = get_Objects($nlp);
	$full_object = array();
	$ordnung = 'praeverb';
	for($x = 0; $x < count($objects);$x++) {
		$collect = array();
		if(isset($objects[$x-1]) && !empty($objects[$x-1])) {
			$start = -1;
			$ende = $objects[$x]['Satzposition'];
			for($i = $objects[$x]['Satzposition']; $i > ($objects[$x-1]['Satzposition']); $i--) {
				$touch_verb = false;
				if(!preg_match('/(VMFIN|VAFIN|VVFIN)/', implode(' ',$nlp['nlp_array'][$i]['POS_Summe']))) {
					array_push($collect, $nlp['nlp_array'][$i]['Wort']);
				} else {
				   $touch_verb = true;
				   $ordnung = 'postverb';
				}					
			}
			if($touch_verb == true) {
				$start = $objects[$x-1]['Satzposition']+2;
			} else {
				$start = $objects[$x-1]['Satzposition']+1;
			}
			$full_object[] = array('Satzglied' => implode(' ', array_reverse($collect)), 'start' => $start, 'ende' => $ende, 'Ordnung' => $ordnung);		
		} else {
			for($i = $objects[$x]['Satzposition']; $i > -1; $i--) {
				array_push($collect, $nlp['nlp_array'][$i]['Wort']);				
			}
			$full_object[] = array('Satzglied' => implode(' ', array_reverse($collect)), 'start' => 0, 'ende' => $objects[$x]['Satzposition'], 'Ordnung' => $ordnung);				
		}
	}
	return $full_object;
}


function Satztyp($nlp_string) {
     $satztyp = '';
	 if(preg_match('/^<[[A-Za-z\|]{0,5}(PWS|PWAT|PWAV|VMFIN|VVFIN)/', $nlp_string) && !preg_match('/.*(NN|NE|PPER)(>|\|)\s<[A-Za-z\|]{0,5}(VMFIN|VAFIN|VVFIN|VVFIN).*/', $nlp_string)) {
		$satztyp = 'Frage';
	 } elseif(preg_match('/.*(NN|NE|PPER)(>|\|)\s<[A-Za-z\|]{0,5}(VMFIN|VAFIN|VVFIN).*/', $nlp_string)) {
		$satztyp = 'Aussage';
	 } elseif(preg_match('/^(<|\|)(VVIMP|PTKVZ )/', $nlp_string)) {
		$satztyp = 'Befehl';	 
	 }
	 return $satztyp;
}

function CREATE_ARRAY() {
	global $nlp_array;	
	foreach($nlp_array as $grammatik_zeile) {		
		$zeile = preg_split("/[\s]+/", $grammatik_zeile);
		//exit(var_dump($zeile));
		if(isset($zeile[0]) && isset($zeile[1]) && isset($zeile[2])) {
			$wort_der_zeile = $zeile[0];
			//exit($wort_der_zeile);
			$wort_grundform = $zeile[1];
			$grammatik_der_zeile = $zeile[2];
			//if(strcasecmp($wort, $wort_der_zeile) == 0) {				
				$grammatik[] = array( 
					'POS' => addslashes($grammatik_der_zeile), 
					'Grundform'  => addslashes($wort_grundform),
					'Begriff' => $wort_der_zeile); 
		}
	} 
    file_put_contents('Grammatik.php', '<?php $Grammtik=' .var_export($grammatik, true).'?>');		
}


	//Begriffskombinatorik
function AlleBegriffsAnordnungenEinesObjects($Wort_Array_des_Objekts) {
	$Kombinationen = array();
	$Permutationen = pc_permute($Wort_Array_des_Objekts);
	foreach($Permutationen as $arr) {
		$Kombinationen[] = trim(implode(' ', $arr));
		$str = '';
		for($i = 0; $i < count($arr)-1; $i++) {
			$str .= ' '.$arr[$i];
			$Kombinationen[] = trim($str);
		}
	}
	return array_unique($Kombinationen);
}

function pc_permute($items, $perms = array( )) {
    if (empty($items)) {
        $return = array($perms);
    }  else {
        $return = array();
        for ($i = count($items) - 1; $i >= 0; --$i) {
             $newitems = $items;
             $newperms = $perms;
         list($foo) = array_splice($newitems, $i, 1);
             array_unshift($newperms, $foo);
             $return = array_merge($return, pc_permute($newitems, $newperms));
         }
    }
    return $return;
}



?>