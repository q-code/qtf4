<h2>Introductie</h2>

<div style="padding:10px 0 10px 15px">
<p>De <b>tags</b> zijn markeringen de gebruiker aan een bericht kan vastmaken. Deze tags worden om berichten groepen of specifieke statistieken te produceren. De tags een gebruiker kan vastmaken is vrij. Niettemin, als een lijst van tags wordt bepaald, zal het systeem deze <b>voorgestelde tags</b> in een drop-down lijst tonen.</p>
<p>De lijst van deze voorgestelde tags kan per forum (en per taal) worden bepaald. De lijsten zijn eenvoudige <b>csv</b> files die in de /upload/ map worden opgeslagen.</p>
</div>

<h2>CSV file</h2>

<div style="padding:10px 0 10px 15px">
<p>De lijst van tags is een text file met een tag per lijn.<br>
Een tag en, naar keuze, een korte beschrijving hebben, na een punkomma.
<p>PAS OP: Een tag mag geen aanhalingstekens ["] bevatten. Als u accentkarakters gebruikt, zeker ben dat uw file in utf-8 gecodeerd is.</p>
<p>Voorbeeld:</p>
<p style="color:#139613">
Internationale<br>
Nationaal<br>
Lokaal;Staat of gemeente
</p>
</div>

<h2>Om een lijst van tags te cre&euml;ren gemeenschappelijk voor alle forums</h2>

<div style="padding:10px 0 10px 15px">
<p>
1 - Een .csv file maken, met naam <b>tags_en.csv</b><br>
2 - De file door de beheer pagina uploaden (of door ftp software)
</p>
<p class="small">Nota: _nl betekend nederlands, voor andere talen: tags_fr.csv, tags_en.csv, ...</p>
</div>

<h2>Om een lijst van tags te cre&euml;ren specifiek voor een forum</h2>

<div style="padding:10px 0 10px 15px">
<p>
1 - Maken een .csv file named <b>tags_en_0.csv</b> waar "0" is de sectie-id.<br>
2 - De file door de beheer pagina uploaden (of door ftp software)
</p>
<p class="small">Nota: _nl betekend nederlands, voor andere talen: tags_fr_0.csv, tags_en_0.csv, ...</p>
</div>
