<?php
session_start();
//ini_set('memory_limit', '10000M');
include_once('Satzanalyse.php');

if(isset($_REQUEST['refresh']) && $_REQUEST['refresh'] == 1) {
	session_unset();
}


$nlp = NLP_Extraktion($_REQUEST['q']);
exit(json_encode($nlp));

/*$_SESSION['NLP'][] = $output['nlp_string'];

highlight_string("<?php\n\$data =\n" . var_export($_SESSION['NLP'], true). ";\n?>");
highlight_string("<?php\n\$data =\n" . var_export(Inhalts_Extraktion($output), true). ";\n?>");
highlight_string("<?php\n\$data =\n" . var_export($output, true). ";\n?>");
*/


?>
