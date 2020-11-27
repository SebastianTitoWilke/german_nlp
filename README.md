# german_nlp
fast - German - Natural Language Processing with PHP and for Ajax/JSON

1. Put those files in one dir
2. index.php?q=your%20german%20sentence
3. get the answer in JSON ...

For Example

Request: index.php?q=Ich%20bin%20ein%20Programm

Answer: {"UserIP":"::1",
         "Satz":"Ich bin ein Programm",
         "Satztyp":"Aussage",
         "nlp_time":1606482482,
         "nlp_string":" ",
         "nlp_array":[{"Wort":"Ich","Grammatik":[{"POS":"PPER","Grundform":"ich"}],"POS_Summe":["PPER"]},{
                       "Wort":"bin","Grammatik":[{"POS":"VAFIN","Grundform":"sein"}],"POS_Summe":["VAFIN"]},{
                       "Wort":"ein","Grammatik":[{"POS":"ART","Grundform":"eine"},{"POS":"PTKVZ","Grundform":"ein"},{"POS":"CARD","Grundform":"ein"}], "POS_Summe":["ART","PTKVZ","CARD"]},{
                       "Wort":"Programm","Grammatik":[{"POS":"NN","Grundform":"Programm"}],"POS_Summe":["NN"]}]}

Satztyp = Frage (Question) | Aussage (Statement) | Befehl (Command)

Find the possible NLP-Tagger-Codes in POS and POS_Summe (for meaning watch the SSTT_TagSET.pdf)

And just for fun, the Question-Answer-Machine:

frage_antwort_maschine.php?q=Statment and than Question to Statement frage_antwort_maschine.php?q=Question

Feel free to change anything ...

mfg
Tito
https://github.com/SebastianTitoWilke/german_nlp


