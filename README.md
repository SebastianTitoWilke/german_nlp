# german_nlp
fast - German - Natural Language Processing in PHP that answers in JSON

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

Satztyp (sentence type) = Frage (question) | Aussage (statement) | Befehl (command)

Find the possible NLP-Tagger-Codes in POS and POS_Summe (for meaning watch the STTS_Tagset_Tiger (1).pdf)

--------------------------------------------------------------------------------------------------------------------------------------------------------------------------

And just for fun, the Question-Answer-Machine:

1. Save short Informations-Sentence: frage_antwort_maschine.php?q=Statment 
2. Question to Informations: frage_antwort_maschine.php?q=Question
3. Get the Answer ...
4. To delete the Session frage_antwort_maschine.php?q=&refresh=1

but, a warning, this Question-Answer-Machine is not ready and the machine creates a lot of errors depending on some of the possible sentences that exists in german! 
You can try to fix them all :D even, "wenn ... dann ..." is not there, and, maybe, if you add syllogisms as conclusions by a number of sentences, with the same subject or object, like Modus Barbara, Modus tollens, "Transitive Relationen" and all those things, please show me the result :(

Do what you like, feel free to change anything ... you can delete the file if you want

mfg

Tito

Github: https://github.com/SebastianTitoWilke 

Facebook: https://www.facebook.com/sebastian.wilke.3139 

Twitter: https://twitter.com/TheSickUniverse

--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
All Code and Files from:

https://github.com/SebastianTitoWilke/german_nlp



